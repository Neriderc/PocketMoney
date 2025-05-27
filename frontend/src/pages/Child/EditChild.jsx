import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";
import ScheduledTransactionTable from "../../components/Child/ScheduledTransactionTable.jsx";

export default function EditChildPage() {
    const { householdId, childId } = useParams();
    const navigate = useNavigate();
    const [child, setChild] = useState(null);
    const [accounts, setAccounts] = useState([]);
    const [newName, setNewName] = useState("");
    const [newDateOfBirth, setNewDateOfBirth] = useState("");
    const [showChildDeleteModal, setShowChildDeleteModal] = useState(false);
    const [showAccountDeleteModal, setShowAccountDeleteModal] = useState(false);
    const [accountToDelete, setAccountToDelete] = useState(null);
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("");
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        apiFetch(`children/${childId}`)
            .then((response) => response.json())
            .then((data) => {
                setChild(data);
                setNewName(data.name);
                setNewDateOfBirth(
                    data.dateOfBirth ? data.dateOfBirth.split("T")[0] : "",
                );
            })
            .catch((error) =>
                console.error("Error fetching child data:", error),
            );

        apiFetch(`children/${childId}/accounts`)
            .then((response) => response.json())
            .then((data) => {
                if (data.member && Array.isArray(data.member)) {
                    const accounts = data.member.flatMap(
                        (item) => item.accounts || [],
                    );
                    setAccounts(accounts);
                }
            })
            .catch((error) =>
                console.error("Error fetching accounts data:", error),
            );
    }, [childId, navigate]);

    const handleUpdateDetails = () => {
        const payload = {
            name: newName,
            dateOfBirth: newDateOfBirth || null,
        };

        apiFetch(`children/${childId}`, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/merge-patch+json",
            },
            body: JSON.stringify(payload),
        })
            .then((response) => {
                if (response.ok) {
                    setChild({
                        ...child,
                        name: newName,
                        dateOfBirth: newDateOfBirth || null,
                    });
                    setMessage("Child details updated successfully!");
                    setMessageType("success");
                } else {
                    setMessage("Error updating child details.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error updating child details.");
                setMessageType("danger");
            });
    };

    const handleDeleteChild = () => {
        apiFetch(`children/${childId}`, {
            method: "DELETE",
        })
            .then((response) => {
                if (response.ok) {
                    navigate("/dashboard");
                } else {
                    setMessage("Error deleting child.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error deleting child.");
                setMessageType("danger");
            });
    };

    const handleDeleteAccount = (accountUrl) => {
        const accountId = accountUrl.split("/").pop();

        apiFetch(`children/${childId}/accounts/${accountId}`, {
            method: "DELETE",
        })
            .then((response) => {
                if (response.ok) {
                    setAccounts(
                        accounts.filter(
                            (account) => account["@id"] !== accountUrl,
                        ),
                    );
                    setMessage("Account deleted successfully!");
                    setMessageType("success");
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

    const openChildDeleteModal = () => setShowChildDeleteModal(true);
    const closeChildDeleteModal = () => setShowChildDeleteModal(false);

    const openAccountDeleteModal = (accountUrl) => {
        setAccountToDelete(accountUrl);
        setShowAccountDeleteModal(true);
    };

    const closeAccountDeleteModal = () => {
        setAccountToDelete(null);
        setShowAccountDeleteModal(false);
    };

    const confirmDeleteAccount = () => {
        if (accountToDelete) {
            handleDeleteAccount(accountToDelete);
            closeAccountDeleteModal();
        }
    };

    const handleViewAccount = (fullAccountId) => {
        const accountId = fullAccountId.split("/").pop();
        navigate(
            `/household/${householdId}/child/${childId}/account/${accountId}`,
        );
    };
    const handleBackClick = () => {
        navigate(`/household/${householdId}/child/${childId}`);
    };

    function handleCreateAccount() {
        navigate(`/household/${householdId}/child/${childId}/account/add`);
    }

    if (!child) {
        return (
            <div>
                <NavBar />
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
                            onClick={handleBackClick}
                            className="btn btn-secondary mb-3"
                        >
                            &larr; Back to Child Summary
                        </button>
                        <h5 className="card-title">Edit Child Details</h5>
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
                            <label>Date of Birth</label>
                            <input
                                type="date"
                                className="form-control"
                                value={newDateOfBirth}
                                onChange={(e) =>
                                    setNewDateOfBirth(e.target.value)
                                }
                            />
                        </div>
                        <button
                            className="btn btn-primary mt-2"
                            onClick={handleUpdateDetails}
                        >
                            Update Details
                        </button>
                    </div>
                </div>

                <div
                    className="card shadow-sm border-0 mb-4"
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <div className="card-body">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <h5 className="card-title">Accounts</h5>
                            <button
                                onClick={handleCreateAccount}
                                className="btn btn-success"
                            >
                                + Add account
                            </button>
                        </div>
                        <ul className="list-unstyled">
                            {accounts.map((account) => {
                                return (
                                    <li
                                        key={account["@id"]}
                                        className="mb-3 card"
                                    >
                                        <div className="card-body d-flex justify-content-between align-items-center">
                                            <span>{account.name}</span>
                                            <div className="d-flex">
                                                <button
                                                    onClick={() =>
                                                        handleViewAccount(
                                                            account["@id"],
                                                        )
                                                    }
                                                    className="btn btn-primary btn-sm me-2"
                                                >
                                                    View
                                                </button>
                                                <button
                                                    className="btn btn-sm btn-danger"
                                                    onClick={() =>
                                                        openAccountDeleteModal(
                                                            account["@id"],
                                                        )
                                                    }
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                </div>

                <div
                    className="card shadow-sm border-0 mb-4"
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <ScheduledTransactionTable />
                </div>
                <div className="card border-danger mb-4">
                    <div className="card-body text-danger">
                        <h5 className="card-title">Delete Child</h5>
                        <p>This action cannot be undone.</p>
                        <button
                            className="btn btn-danger mt-2"
                            onClick={openChildDeleteModal}
                        >
                            Delete Child
                        </button>
                    </div>
                </div>

                {/* Child Deletion Modal */}
                <div
                    className={`modal fade ${showChildDeleteModal ? "show" : ""}`}
                    style={{ display: showChildDeleteModal ? "block" : "none" }}
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
                                    onClick={closeChildDeleteModal}
                                ></button>
                            </div>
                            <div className="modal-body">
                                <p>
                                    Are you sure you want to delete this child?
                                    This action cannot be undone.
                                </p>
                            </div>
                            <div className="modal-footer">
                                <button
                                    type="button"
                                    className="btn btn-secondary"
                                    onClick={closeChildDeleteModal}
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-danger"
                                    onClick={handleDeleteChild}
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                {showChildDeleteModal && (
                    <div className="modal-backdrop fade show"></div>
                )}

                {/* Account Deletion Modal */}
                <div
                    className={`modal fade ${showAccountDeleteModal ? "show" : ""}`}
                    style={{
                        display: showAccountDeleteModal ? "block" : "none",
                    }}
                    tabIndex="-1"
                >
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">
                                    Confirm Account Deletion
                                </h5>
                                <button
                                    type="button"
                                    className="btn-close"
                                    onClick={closeAccountDeleteModal}
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
                                    onClick={closeAccountDeleteModal}
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-danger"
                                    onClick={confirmDeleteAccount}
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                {showAccountDeleteModal && (
                    <div className="modal-backdrop fade show"></div>
                )}
            </div>
        </div>
    );
}
