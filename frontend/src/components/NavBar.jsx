import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAppContext } from "../context/AppContext";

export default function NavBar() {
    const navigate = useNavigate();
    const { activeHousehold, setActiveHousehold, user, logout, apiFetch } =
        useAppContext();
    const [households, setHouseholds] = useState([]);

    useEffect(() => {
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
        <nav className="navbar navbar-expand-md navbar-dark bg-dark">
            <div className="container-fluid">
                {/* Logo */}
                <span
                    className="navbar-brand fs-3 d-flex align-items-center brand-title"
                    style={{
                        cursor: "pointer",
                    }}
                    onClick={handleLogoClick}
                >
                    <i className="bi bi-piggy-bank me-2"></i>
                    Pocket Money Tracker
                </span>

                {/* Toggler */}
                <button
                    className="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarContent"
                    aria-controls="navbarContent"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span className="navbar-toggler-icon"></span>
                </button>

                {/* Collapsible Content */}
                <div className="collapse navbar-collapse" id="navbarContent">
                    <ul className="navbar-nav ms-auto align-items-md-center gap-2">
                        {user && (
                            <li className="nav-item text-white">
                                Welcome, {user.username}!
                            </li>
                        )}
                        {households.length > 1 && (
                            <li className="nav-item dropdown">
                                <button
                                    className="btn btn-outline-light dropdown-toggle"
                                    id="householdDropdown"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    {activeHousehold
                                        ? activeHousehold.name
                                        : "Select Household"}
                                </button>
                                <ul
                                    className="dropdown-menu dropdown-menu-end"
                                    aria-labelledby="householdDropdown"
                                >
                                    {households.map((household) => (
                                        <li key={household.id}>
                                            <button
                                                className="dropdown-item"
                                                onClick={() =>
                                                    handleSwitchHousehold(
                                                        household.id,
                                                    )
                                                }
                                            >
                                                {household.name}
                                            </button>
                                        </li>
                                    ))}
                                </ul>
                            </li>
                        )}
                        <li className="nav-item">
                            <button
                                className="btn btn-outline-light"
                                onClick={handleSettingsClick}
                            >
                                <i className="bi bi-gear"></i>
                                Settings
                            </button>
                        </li>
                        <li className="nav-item">
                            <button
                                className="btn btn-outline-light"
                                onClick={logout}
                            >
                                <i className="bi bi-box-arrow-right"></i>
                                Logout
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    );
}
