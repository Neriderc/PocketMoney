import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";

export default function UserDetailsPage() {
    const { userId } = useParams();
    const navigate = useNavigate();
    const [user, setUser] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        apiFetch(`users/${userId}`, logout, {
            method: "GET",
        })
            .then((response) => response.json())
            .then((data) => {
                setUser(data);
                setIsLoading(false);
            })
            .catch((error) => {
                console.error("Error fetching user details:", error);
                setIsLoading(false);
            });
    }, [userId]);

    const handleBack = () => {
        navigate("/settings");
    };
    const handleEditUser = () => {
        navigate(`/user/${userId}/edit`);
    };

    if (isLoading) {
        return (
            <div>
                <NavBar />
                <div className="container mt-5 text-center">
                    <div className="spinner-border text-primary" role="status">
                        <span className="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        );
    }

    if (!user) {
        return (
            <div>
                <NavBar />
                <div className="container mt-5">
                    <div className="alert alert-danger" role="alert">
                        User not found.
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div>
            <NavBar />
            <div className="container mt-5">
                <div
                    className="card shadow-sm border-0"
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <div className="card-body">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <button
                                onClick={handleBack}
                                className="btn btn-secondary"
                            >
                                &larr; Back to settings
                            </button>
                            <button
                                onClick={handleEditUser}
                                className="btn btn-outline-secondary"
                            >
                                Edit user
                            </button>
                        </div>
                        <h3 className="card-title text-primary">
                            User Details
                        </h3>
                        <div className="mt-3">
                            <div className="mb-3">
                                <strong>Username:</strong> {user.username}
                            </div>
                            <div className="mb-3">
                                <strong>Email:</strong> {user.email}
                            </div>
                            <div className="mb-3">
                                <strong>Roles:</strong> {user.roles?.join(", ")}
                            </div>
                            <div className="mb-3">
                                <strong>Linked to child:</strong>{" "}
                                {user.linkedChild?.name || "None"}
                            </div>
                            <div className="mb-3">
                                <strong>Default Household:</strong>{" "}
                                {user.defaultHousehold?.name ||
                                    user.defaultHousehold?.["@id"] ||
                                    "None"}
                            </div>
                            <div className="mb-3">
                                <strong>Households:</strong>
                                <ul>
                                    {user.households?.map((hh) => (
                                        <li key={hh["@id"] || hh.id}>
                                            {hh.name || hh["@id"]}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
