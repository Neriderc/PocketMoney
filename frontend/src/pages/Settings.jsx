import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAppContext } from "../context/AppContext";
import NavBar from "../components/NavBar";

export default function SettingsPage() {
    const navigate = useNavigate();
    const { isAdmin, user, apiFetch, logout } = useAppContext();
    const [households, setHouseholds] = useState([]);
    const [users, setUsers] = useState([]);
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("");
    const [defaultHousehold, setDefaultHousehold] = useState(null);

    useEffect(() => {
        apiFetch("households", logout)
            .then((response) => response.json())
            .then((data) => {
                if (data.member && Array.isArray(data.member)) {
                    setHouseholds(data.member);
                }
            })
            .catch((error) =>
                console.error("Error fetching households:", error),
            );

        if (isAdmin()) {
            apiFetch("users", logout)
                .then((response) => response.json())
                .then((usersData) => {
                    if (usersData.member && Array.isArray(usersData.member)) {
                        setUsers(usersData.member);
                    }
                })
                .catch((error) =>
                    console.error("Error fetching users:", error),
                );
        }

        // Set default household from user context
        if (user?.defaultHousehold) {
            setDefaultHousehold(user.defaultHousehold.id);
        }
    }, [isAdmin, user]);

    const handleCreateHousehold = () => navigate("/household/add");
    const handleCreateUser = () => navigate("/user/add");
    const handleViewHousehold = (householdId) =>
        navigate(`/household/${householdId}`);
    const handleViewUser = (userId) => navigate(`/user/${userId}`);
    const handleBackToDashboard = () => navigate("/dashboard");

    const handleDefaultHouseholdChange = (householdId) => {
        apiFetch("users/me", logout, {
            method: "PATCH",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ defaultHouseholdId: householdId }),
        })
            .then((response) => {
                if (!response.ok)
                    throw new Error("Failed to update default household");
                return response.json();
            })
            .then((data) => {
                setMessage("Default household updated successfully");
                setMessageType("success");
                setDefaultHousehold(householdId);
            })
            .catch((error) => {
                console.error("Error updating default household:", error);
                setMessage(error.message || "Error updating default household");
                setMessageType("danger");
            });
    };

    return (
        <div>
            <NavBar />
            <div className="container mt-5">
                {message && (
                    <div
                        className={`alert alert-${messageType} mt-3`}
                        role="alert"
                    >
                        {message}
                    </div>
                )}

                {/* Default Household Section */}
                <div
                    className="card shadow-sm border-0 mb-4"
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <div className="card-body">
                        <button
                            onClick={handleBackToDashboard}
                            className="btn btn-secondary mb-3"
                        >
                            &larr; Back to dashboard
                        </button>
                        <div className="mb-3">
                            <h3 className="card-title text-primary">
                                Settings
                            </h3>
                        </div>
                        <div className="mb-3">
                            <label
                                htmlFor="defaultHousehold"
                                className="form-label"
                            >
                                Default Household
                            </label>
                            <select
                                className="form-select"
                                id="defaultHousehold"
                                value={defaultHousehold || ""}
                                onChange={(e) =>
                                    handleDefaultHouseholdChange(e.target.value)
                                }
                            >
                                <option value="">
                                    Select a default household
                                </option>
                                {households.map((household) => (
                                    <option
                                        key={household.id}
                                        value={household.id}
                                    >
                                        {household.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>
                </div>

                {/* Households Section */}
                <div
                    className="card shadow-sm border-0 mb-4"
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <div className="card-body">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <h3 className="card-title text-primary">
                                Households
                            </h3>
                            {isAdmin() && (
                                <button
                                    onClick={handleCreateHousehold}
                                    className="btn btn-success"
                                >
                                    + Add household
                                </button>
                            )}
                        </div>
                        <ul className="list-unstyled">
                            {households.map((household) => (
                                <li key={household.id} className="mb-3 card">
                                    <div className="card-body d-flex justify-content-between align-items-center">
                                        <span>{household.name}</span>
                                        <button
                                            className="btn btn-sm btn-primary"
                                            onClick={() =>
                                                handleViewHousehold(
                                                    household.id,
                                                )
                                            }
                                        >
                                            View
                                        </button>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>

                {/* Users Section */}
                {isAdmin() && (
                    <div
                        className="card shadow-sm border-0 mb-4"
                        style={{ backgroundColor: "#f8f9fa" }}
                    >
                        <div className="card-body">
                            <div className="d-flex justify-content-between align-items-center mb-3">
                                <h3 className="card-title text-primary">
                                    Users
                                </h3>
                                <button
                                    onClick={handleCreateUser}
                                    className="btn btn-success"
                                >
                                    + Add user
                                </button>
                            </div>
                            <ul className="list-unstyled">
                                {users.map((user) => (
                                    <li key={user.id} className="mb-3 card">
                                        <div className="card-body d-flex justify-content-between align-items-center">
                                            <span>{user.username}</span>
                                            <button
                                                className="btn btn-sm btn-primary"
                                                onClick={() =>
                                                    handleViewUser(user.id)
                                                }
                                            >
                                                View
                                            </button>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
