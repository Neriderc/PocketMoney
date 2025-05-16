import React, { createContext, useState, useContext, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import axios from "axios";
import { apiFetch as rawApiFetch } from "../utils/utils.js";

const AppContext = createContext();

export const AppProvider = ({ children }) => {
    const [activeHousehold, setActiveHousehold] = useState(() => {
        const storedHousehold = localStorage.getItem("activeHousehold");
        return storedHousehold ? JSON.parse(storedHousehold) : null;
    });

    const [user, setUser] = useState(null);
    const [authChecked, setAuthChecked] = useState(false);
    const navigate = useNavigate();
    const API_BASE_URL = "api";

    const isAdmin = () => {
        return user?.roles?.includes("ROLE_ADMIN");
    };

    const isAuthenticated = () => {
        return user !== null;
    };

    const apiFetch = (endpoint, options = {}) => {
        return rawApiFetch(endpoint, logout, options);
    };

    const login = async (credentials) => {
        const response = await axios.post(
            `${API_BASE_URL}/login_check`,
            credentials,
        );

        const token = response.data.token;
        localStorage.setItem("access_token", token);

        const userResponse = await axios.get(`${API_BASE_URL}/users/me`, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });

        setUser(userResponse.data);
        setAuthChecked(true);

        return {
            token,
            user: userResponse.data,
        };
    };

    const logout = () => {
        localStorage.removeItem("access_token");
        localStorage.removeItem("activeHousehold");
        setUser(null);
        setActiveHousehold(null);
        setAuthChecked(null);
        navigate("/login");
    };

    useEffect(() => {
        const token = localStorage.getItem("access_token");
        if (token && !authChecked) {
            console.log(token);
            apiFetch("users/me")
                .then((response) => response.json())
                .then((data) => {
                    setUser(data);
                    if (!activeHousehold && data.defaultHousehold) {
                        setActiveHousehold(data.defaultHousehold);
                    }
                })
                .catch((error) => {
                    console.error("Error fetching user details:", error);
                    logout();
                })
                .finally(() => {
                    setAuthChecked(true);
                });
        } else {
            setAuthChecked(true);
        }
    }, [activeHousehold, user, authChecked]);

    useEffect(() => {
        if (activeHousehold) {
            localStorage.setItem(
                "activeHousehold",
                JSON.stringify(activeHousehold),
            );
        } else {
            localStorage.removeItem("activeHousehold");
        }
    }, [activeHousehold, user]);

    return (
        <AppContext.Provider
            value={{
                activeHousehold,
                setActiveHousehold,
                user,
                setUser,
                isAdmin,
                isAuthenticated,
                login,
                logout,
                authChecked,
                apiFetch,
            }}
        >
            {children}
        </AppContext.Provider>
    );
};

export const useAppContext = () => useContext(AppContext);
