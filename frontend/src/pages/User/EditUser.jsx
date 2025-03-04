import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import Select from "react-select";
import { useAppContext } from "../../context/AppContext.jsx";

export default function EditUserPage() {
    const { userId } = useParams();
    const navigate = useNavigate();
    const [username, setUsername] = useState("");
    const [email, setEmail] = useState("");
    const [roles, setRoles] = useState([]);
    const [households, setHouseholds] = useState([]);
    const [selectedHouseholds, setSelectedHouseholds] = useState([]);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("");
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const { apiFetch, logout } = useAppContext();

    const allRoles = [
        { value: "ROLE_USER", label: "User" },
        { value: "ROLE_ADMIN", label: "Admin" },
    ];

    useEffect(() => {
        apiFetch("households", logout)
            .then((res) => res.json())
            .then((data) => setHouseholds(data.member || []))
            .catch((err) => {
                console.error("Failed to load households", err);
                setMessage("Failed to load households.");
                setMessageType("danger");
            });

        apiFetch(`users/${userId}`, logout)
            .then((res) => res.json())
            .then((data) => {
                setUsername(data.username);
                setEmail(data.email);
                setRoles(data.roles || []);
                setSelectedHouseholds(
                    (data.households || []).map((h) => h["@id"]),
                );
            })
            .catch((error) => {
                console.error("Error loading user:", error);
                setMessage("Failed to load user.");
                setMessageType("danger");
            });
    }, [userId]);

    const handleSubmit = (event) => {
        event.preventDefault();
        setIsSubmitting(true);
        setMessage("");
        setMessageType("");

        const validHouseholdIds = households
            ? households.map((h) => h["@id"])
            : [];

        const filteredHouseholds = selectedHouseholds.filter((id) =>
            validHouseholdIds.includes(id),
        );

        apiFetch(`users/${userId}`, logout, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/merge-patch+json",
            },
            body: JSON.stringify({
                username,
                email,
                roles,
                households: filteredHouseholds,
            }),
        })
            .then(async (response) => {
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(
                        errorData["description"] || "Update failed.",
                    );
                }
                return response.json();
            })
            .then(() => {
                navigate(`/user/${userId}`);
            })
            .catch((error) => {
                console.error("Error updating user:", error);
                setMessage(error.message || "An error occurred.");
                setMessageType("danger");
                setIsSubmitting(false);
            });
    };

    const handleDelete = () => {
        apiFetch(`users/${userId}`, logout, {
            method: "DELETE",
        })
            .then((response) => {
                if (response.ok) {
                    navigate("/settings");
                } else {
                    setMessage("Error deleting user.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error deleting user.");
                setMessageType("danger");
            });
    };

    return (
        <div>
            <NavBar />
            <div className="container mt-5">
                {message && (
                    <div className={`alert alert-${messageType}`} role="alert">
                        {message}
                    </div>
                )}
                <div className="card shadow-sm border-0 mb-4">
                    <div className="card-body">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <button
                                onClick={() => navigate(`/user/${userId}`)}
                                className="btn btn-secondary"
                            >
                                &larr; Back to User
                            </button>
                        </div>
                        <h3 className="card-title text-primary mb-4">
                            Edit User
                        </h3>
                        <form onSubmit={handleSubmit}>
                            <div className="mb-3">
                                <label
                                    htmlFor="username"
                                    className="form-label"
                                >
                                    Username
                                </label>
                                <input
                                    type="text"
                                    className="form-control"
                                    id="username"
                                    value={username}
                                    onChange={(e) =>
                                        setUsername(e.target.value)
                                    }
                                    required
                                />
                            </div>
                            <div className="mb-3">
                                <label htmlFor="email" className="form-label">
                                    Email
                                </label>
                                <input
                                    type="email"
                                    className="form-control"
                                    id="email"
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                />
                            </div>
                            <div className="mb-3">
                                <label className="form-label">Roles</label>
                                <Select
                                    isMulti
                                    options={allRoles}
                                    value={allRoles.filter((role) =>
                                        roles.includes(role.value),
                                    )}
                                    onChange={(selectedOptions) => {
                                        setRoles(
                                            selectedOptions.map(
                                                (opt) => opt.value,
                                            ),
                                        );
                                    }}
                                    classNamePrefix="react-select"
                                />
                            </div>

                            <div className="mb-3">
                                <label className="form-label">Households</label>
                                <Select
                                    isMulti
                                    options={households.map((h) => ({
                                        value: h["@id"],
                                        label: h.name,
                                    }))}
                                    value={households
                                        .filter((h) =>
                                            selectedHouseholds.includes(
                                                h["@id"],
                                            ),
                                        )
                                        .map((h) => ({
                                            value: h["@id"],
                                            label: h.name,
                                        }))}
                                    onChange={(selectedOptions) =>
                                        setSelectedHouseholds(
                                            selectedOptions.map(
                                                (opt) => opt.value,
                                            ),
                                        )
                                    }
                                    classNamePrefix="react-select"
                                />
                            </div>

                            <button
                                type="submit"
                                className="btn btn-primary"
                                disabled={isSubmitting}
                            >
                                {isSubmitting ? "Updating..." : "Update"}
                            </button>
                        </form>
                    </div>
                </div>

                <div className="card border-danger mb-4">
                    <div className="card-body text-danger">
                        <h5 className="card-title">Delete User</h5>
                        <p>This action cannot be undone.</p>
                        <button
                            className="btn btn-danger"
                            onClick={() => setShowDeleteModal(true)}
                        >
                            Delete User
                        </button>
                    </div>
                </div>

                {showDeleteModal && (
                    <div
                        className="modal show fade d-block"
                        tabIndex="-1"
                        role="dialog"
                    >
                        <div className="modal-dialog" role="document">
                            <div className="modal-content">
                                <div className="modal-header">
                                    <h5 className="modal-title">
                                        Confirm Delete
                                    </h5>
                                    <button
                                        type="button"
                                        className="btn-close"
                                        onClick={() =>
                                            setShowDeleteModal(false)
                                        }
                                    />
                                </div>
                                <div className="modal-body">
                                    <p>
                                        Are you sure you want to delete this
                                        user?
                                    </p>
                                </div>
                                <div className="modal-footer">
                                    <button
                                        type="button"
                                        className="btn btn-secondary"
                                        onClick={() =>
                                            setShowDeleteModal(false)
                                        }
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="button"
                                        className="btn btn-danger"
                                        onClick={handleDelete}
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
