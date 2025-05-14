import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";

export default function EditHouseholdPage() {
    const { householdId } = useParams();
    const navigate = useNavigate();
    const [household, setHousehold] = useState(null);
    const [newName, setNewName] = useState("");
    const [newDescription, setNewDescription] = useState("");
    const [showDeleteModal, setShowDeleteModal] = useState(false);
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
                setNewName(data.name);
                setNewDescription(data.description || "");
            })
            .catch((error) =>
                console.error("Error fetching household data:", error),
            );
    }, [householdId, navigate]);

    const handleUpdateHousehold = () => {
        const payload = {
            name: newName,
            description: newDescription,
        };

        apiFetch(`households/${householdId}`, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/merge-patch+json",
            },
            body: JSON.stringify(payload),
        })
            .then((response) => {
                if (response.ok) {
                    setMessage("Household updated successfully!");
                    setMessageType("success");
                } else {
                    setMessage("Error updating household.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error updating household.");
                setMessageType("danger");
            });
    };

    const handleDeleteHousehold = () => {
        apiFetch(`households/${householdId}`, {
            method: "DELETE",
        })
            .then((response) => {
                if (response.ok) {
                    navigate("/settings");
                } else {
                    setMessage("Error deleting household.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error deleting household.");
                setMessageType("danger");
            });
    };

    const handleClickBack = () => {
        navigate(`/household/${householdId}`);
    };

    const openDeleteModal = () => setShowDeleteModal(true);
    const closeDeleteModal = () => setShowDeleteModal(false);

    if (!household) {
        return (
            <div>
                <NavBar />
                <div className="container mt-5">
                    <div className="text-center">
                        <div
                            className="spinner-border text-primary"
                            role="status"
                        >
                            <span className="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
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
                        <button
                            onClick={handleClickBack}
                            className="btn btn-secondary mb-3"
                        >
                            &larr; Back to Household
                        </button>
                        <h5 className="card-title">Edit Household Details</h5>
                        <div className="form-group">
                            <label>Name</label>
                            <input
                                type="text"
                                className="form-control"
                                value={newName}
                                onChange={(e) => setNewName(e.target.value)}
                            />
                        </div>
                        <div className="form-group">
                            <label>Description</label>
                            <textarea
                                className="form-control"
                                value={newDescription}
                                onChange={(e) =>
                                    setNewDescription(e.target.value)
                                }
                                rows={4}
                            />
                        </div>
                        <button
                            className="btn btn-primary mt-2"
                            onClick={handleUpdateHousehold}
                        >
                            Update Household
                        </button>
                    </div>
                </div>

                <div className="card border-danger mb-4">
                    <div className="card-body text-danger">
                        <h5 className="card-title">Delete Household</h5>
                        <p>This action cannot be undone.</p>
                        <button
                            className="btn btn-danger mt-2"
                            onClick={openDeleteModal}
                        >
                            Delete Household
                        </button>
                    </div>
                </div>

                {/* Deletion Modal */}
                <div
                    className={`modal fade ${showDeleteModal ? "show" : ""}`}
                    style={{ display: showDeleteModal ? "block" : "none" }}
                    tabIndex="-1"
                >
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">
                                    Confirm Deletion
                                </h5>
                                <button
                                    type="button"
                                    className="btn-close"
                                    onClick={closeDeleteModal}
                                ></button>
                            </div>
                            <div className="modal-body">
                                <p>
                                    Are you sure you want to delete this
                                    household? This action cannot be undone.
                                </p>
                            </div>
                            <div className="modal-footer">
                                <button
                                    type="button"
                                    className="btn btn-secondary"
                                    onClick={closeDeleteModal}
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-danger"
                                    onClick={handleDeleteHousehold}
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                {showDeleteModal && (
                    <div className="modal-backdrop fade show"></div>
                )}
            </div>
        </div>
    );
}
