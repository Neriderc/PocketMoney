import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAppContext } from "../context/AppContext";

export default function NavBar() {
    const navigate = useNavigate();
    const { activeHousehold, setActiveHousehold, user, logout, apiFetch } =
        useAppContext();
    const [households, setHouseholds] = useState([]);

    useEffect(() => {
        const token = localStorage.getItem("access_token");
        if (!token) {
            navigate("/login");
            return;
        }

        const fetchHouseholds = async () => {
            try {
                const response = await apiFetch("households", {
                    method: "GET",
                });
                const data = await response.json();
                if (data.member && Array.isArray(data.member)) {
                    setHouseholds(data.member);

                    if (
                        !activeHousehold ||
                        !data.member.some((h) => h.id === activeHousehold.id)
                    ) {
                        console.log("active household no longer exists");
                        const newActiveHousehold =
                            user?.defaultHousehold || data.member[0];
                        setActiveHousehold(newActiveHousehold);
                    }
                }
            } catch (error) {
                console.error("Error fetching households:", error);
            }
        };

        fetchHouseholds();
    }, [activeHousehold, user]);

    const handleLogoClick = () => {
        navigate("/dashboard");
    };

    const handleSettingsClick = () => {
        navigate("/settings");
    };

    const handleSwitchHousehold = (householdId) => {
        const selectedHousehold = households.find((h) => h.id === householdId);
        if (selectedHousehold) {
            setActiveHousehold(selectedHousehold);
        }
    };

    return (
        <div className="bg-dark text-white py-3 position-relative">
            <div
                style={{
                    position: "absolute",
                    top: "50%",
                    left: "20px",
                    transform: "translateY(-50%)",
                    fontSize: "3rem",
                    cursor: "pointer",
                }}
            >
                <i className="bi bi-piggy-bank" onClick={handleLogoClick}></i>
            </div>

            <div className="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <h2 className="mb-0 fs-5 fs-md-3">Pocket Money Tracker</h2>
                <div className="d-flex flex-column flex-md-row align-items-center gap-2">
                    {user && <p className="mb-0">Welcome, {user.username}!</p>}
                    {households.length > 0 && (
                        <div className="dropdown">
                            <button
                                className="btn btn-outline-light dropdown-toggle"
                                type="button"
                                id="householdDropdown"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                {activeHousehold
                                    ? activeHousehold.name
                                    : "Select Household"}
                            </button>
                            <ul
                                className="dropdown-menu"
                                aria-labelledby="householdDropdown"
                            >
                                {households.map((household) => (
                                    <li key={household.id}>
                                        <a
                                            className="dropdown-item"
                                            href="#"
                                            onClick={() =>
                                                handleSwitchHousehold(
                                                    household.id,
                                                )
                                            }
                                        >
                                            {household.name}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                    <button
                        className="btn btn-outline-light"
                        onClick={handleSettingsClick}
                    >
                        Settings
                    </button>
                    <button className="btn btn-outline-light" onClick={logout}>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    );
}
