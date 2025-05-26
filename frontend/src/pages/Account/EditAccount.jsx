import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import Select from "react-select";
import { CirclePicker } from "react-color";
import { useAppContext } from "../../context/AppContext.jsx";
const pickerColours = [
    "#ffffff",
    "#eb9694",
    "#fad0c3",
    "#fef3bd",
    "#c1e1c5",
    "#bedadc",
    "#bed3f3",
    "#d4c4fb",
    "#d9d9d9",
    "#ffe0b2",
    "#ffccbc",
    "#d7ccc8",
    "#cfd8dc",
    "#f0f4c3",
    "#dcedc8",
    "#ffcdd2",
    "#f8bbd0",
    "#e1bee7",
];

export default function EditAccountPage() {
    const { householdId, childId, accountId } = useParams();
    const navigate = useNavigate();
    const [account, setAccount] = useState(null);
    const [newName, setNewName] = useState("");
    const [newIcon, setNewIcon] = useState("");
    const [newColor, setNewColor] = useState("#000000"); // Default color
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("");
    const [iconOptions, setIconOptions] = useState([]);
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        apiFetch(`children/${childId}/accounts/${accountId}`, {
            method: "GET",
        })
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                setAccount(data);
                setNewName(data.name);
                setNewIcon(data.icon || "");
                setNewColor(data.color || "#000000"); // Default to black if no color is set
            })
            .catch((error) =>
                console.error("Error fetching account data:", error),
            );

        fetch("/bootstrap-icons.json")
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((data) => {
                // Transform the object into an array of { value, label }
                const iconArray = Object.keys(data).map((key) => ({
                    value: key,
                    label: data[key],
                }));
                setIconOptions(iconArray);
            })
            .catch((error) =>
                console.error("Error fetching icon options:", error),
            );
    }, [accountId, navigate]);

    const handleUpdateAccount = () => {
        const token = localStorage.getItem("access_token");
        const payload = {
            name: newName,
            icon: newIcon,
            color: newColor,
        };

        apiFetch(`children/${childId}/accounts/${accountId}`, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/merge-patch+json",
            },
            body: JSON.stringify(payload),
        })
            .then((response) => {
                if (response.ok) {
                    setMessage("Account updated successfully!");
                    setMessageType("success");
                } else {
                    setMessage("Error updating account.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error updating account.");
                setMessageType("danger");
            });
    };

    const handleDeleteAccount = () => {
        const token = localStorage.getItem("access_token");
        apiFetch(`children/${childId}/accounts/${accountId}`, {
            method: "DELETE",
            headers: {
                Authorization: `Bearer ${token}`,
            },
        })
            .then((response) => {
                if (response.ok) {
                    navigate(`/household/${householdId}/child/${childId}`);
                } else {
                    setMessage("Error deleting account.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error deleting account.");
                setMessageType("danger");
            });
    };

    function handleClickBack() {
        navigate(
            `/household/${householdId}/child/${childId}/account/${accountId}`,
        );
    }

    const openDeleteModal = () => setShowDeleteModal(true);
    const closeDeleteModal = () => setShowDeleteModal(false);

    if (!account) {
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
                            &larr; Back to Account
                        </button>
                        <h5 className="card-title">Edit Account Details</h5>
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
                            <label>Icon</label>
                            <Select
                                options={iconOptions}
                                value={iconOptions.find(
                                    (option) => option.value === newIcon,
                                )}
                                onChange={(selectedOption) =>
                                    setNewIcon(selectedOption.value)
                                }
                                placeholder="Select an icon"
                                formatOptionLabel={({ label, value }) => (
                                    <div>
                                        <i className={`bi ${value}`}></i>{" "}
                                        {label}
                                    </div>
                                )}
                            />
                        </div>
                        <div className="form-group">
                            <label>Color</label>
                            <div>
                                <CirclePicker
                                    color={newColor}
                                    colors={pickerColours}
                                    onChange={(color) => setNewColor(color.hex)}
                                />
                            </div>
                        </div>
                        <button
                            className="btn btn-primary mt-2"
                            onClick={handleUpdateAccount}
                        >
                            Update Account
                        </button>
                    </div>
                </div>

                <div className="card border-danger mb-4">
                    <div className="card-body text-danger">
                        <h5 className="card-title">Delete Account</h5>
                        <p>This action cannot be undone.</p>
                        <button
                            className="btn btn-danger mt-2"
                            onClick={openDeleteModal}
                        >
                            Delete Account
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
                                    account? This action cannot be undone.
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
                                    onClick={handleDeleteAccount}
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
