import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";

export default function HouseholdDetailsPage() {
    const { householdId } = useParams();
    const navigate = useNavigate();
    const [household, setHousehold] = useState(null);
    const [children, setChildren] = useState([]);
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("");
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        apiFetch(`households/${householdId}`)
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                setHousehold(data);
            })
            .catch((error) =>
                console.error("Error fetching household details:", error),
            );

        apiFetch(`households/${householdId}/children`)
            .then((response) => response.json())
            .then((data) => {
                if (data.member && Array.isArray(data.member)) {
                    setChildren(data.member);
                }
            })
            .catch((error) => console.error("Error fetching children:", error));
    }, [householdId, navigate]);

    const handleAddChild = () => {
        navigate(`/household/${householdId}/child/add`);
    };

    const handleViewChild = (childId) => {
        navigate(`/household/${householdId}/child/${childId}`);
    };
    const handleBackToSettings = () => {
        navigate(`/settings`);
    };
    const handleEditHousehold = () => {
        navigate(`/household/${householdId}/edit`);
    };

    if (!household) {
        return (
            <div>
                <NavBar />
                <div className="container mt-5">Loading...</div>
            </div>
        );
    }

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
                <div
                    className="card shadow-sm border-0 mb-4"
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <div className="card-body">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <button
                                onClick={handleBackToSettings}
                                className="btn btn-secondary"
                            >
                                &larr; Back to settings
                            </button>
                            <button
                                onClick={handleEditHousehold}
                                className="btn btn-outline-secondary mb-3"
                            >
                                Edit household
                            </button>
                        </div>
                        <h3 className="card-title text-primary">
                            {household.name}
                        </h3>
                        <div className="card-text text-secondary">
                            Description:{" "}
                            <div className="text-dark">
                                {household.description}
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    className="card shadow-sm border-0 mb-4"
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <div className="card-body">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <h3 className="card-title text-primary">
                                Children
                            </h3>
                            <button
                                onClick={handleAddChild}
                                className="btn btn-success"
                            >
                                + Add child
                            </button>
                        </div>
                        <ul className="list-unstyled">
                            {children.map((child) => (
                                <li key={child.id} className="mb-3 card">
                                    <div className="card-body d-flex justify-content-between align-items-center">
                                        <span>{child.name}</span>
                                        <button
                                            className="btn btn-sm btn-primary"
                                            onClick={() =>
                                                handleViewChild(child.id)
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
            </div>
        </div>
    );
}
