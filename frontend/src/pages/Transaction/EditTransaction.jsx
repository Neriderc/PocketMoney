import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";

export default function EditTransactionPage() {
    const { householdId, childId, accountId, transactionId } = useParams();
    const navigate = useNavigate();
    const [transaction, setTransaction] = useState(null);
    const [newDescription, setNewDescription] = useState("");
    const [newAmount, setNewAmount] = useState("");
    const [newComment, setNewComment] = useState("");
    const [newTransactionDate, setNewTransactionDate] = useState("");
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("");
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        apiFetch(
            `accounts/${accountId}/transactions/${transactionId}`,
            logout,
            {
                method: "GET",
            },
        )
            .then((response) => response.json())
            .then((data) => {
                setTransaction(data);
                setNewDescription(data.description);
                setNewAmount(data.amount);
                setNewComment(data.comment || "");
                setNewTransactionDate(
                    data.transactionDate
                        ? data.transactionDate.split("T")[0]
                        : "",
                );
            })
            .catch((error) =>
                console.error("Error fetching transaction data:", error),
            );
    }, [accountId, transactionId, navigate]);

    const handleUpdateTransaction = () => {
        const payload = {
            description: newDescription,
            amount: parseFloat(newAmount),
            comment: newComment,
            transactionDate: newTransactionDate || null,
        };

        apiFetch(
            `accounts/${accountId}/transactions/${transactionId}`,
            logout,
            {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/merge-patch+json",
                },
                body: JSON.stringify(payload),
            },
        )
            .then((response) => {
                if (response.ok) {
                    setMessage("Transaction updated successfully!");
                    setMessageType("success");
                } else {
                    setMessage("Error updating transaction.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error updating transaction.");
                setMessageType("danger");
            });
    };

    const handleDeleteTransaction = () => {
        apiFetch(
            `accounts/${accountId}/transactions/${transactionId}`,
            logout,
            {
                method: "DELETE",
            },
        )
            .then((response) => {
                if (response.ok) {
                    navigate(
                        `/household/${householdId}/child/${childId}/account/${accountId}`,
                    );
                } else {
                    setMessage("Error deleting transaction.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error deleting transaction.");
                setMessageType("danger");
            });
    };

    const openDeleteModal = () => setShowDeleteModal(true);
    const closeDeleteModal = () => setShowDeleteModal(false);

    if (!transaction) {
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
                            onClick={() =>
                                navigate(
                                    `/household/${householdId}/child/${childId}/account/${accountId}/transaction/${transactionId}`,
                                )
                            }
                            className="btn btn-secondary mb-3"
                        >
                            &larr; Back to transaction
                        </button>
                        <h5 className="card-title">Edit Transaction Details</h5>
                        <div className="form-group">
                            <label>Description</label>
                            <input
                                type="text"
                                className="form-control"
                                value={newDescription}
                                onChange={(e) =>
                                    setNewDescription(e.target.value)
                                }
                            />
                        </div>
                        <div className="form-group">
                            <label>Amount</label>
                            <input
                                type="number"
                                className="form-control"
                                value={newAmount}
                                onChange={(e) => setNewAmount(e.target.value)}
                            />
                        </div>
                        <div className="form-group">
                            <label>Comment</label>
                            <input
                                type="text"
                                className="form-control"
                                value={newComment}
                                onChange={(e) => setNewComment(e.target.value)}
                            />
                        </div>
                        <div className="form-group">
                            <label>Transaction Date</label>
                            <input
                                type="date"
                                className="form-control"
                                value={newTransactionDate}
                                onChange={(e) =>
                                    setNewTransactionDate(e.target.value)
                                }
                            />
                        </div>
                        <button
                            className="btn btn-primary mt-2"
                            onClick={handleUpdateTransaction}
                        >
                            Update Transaction
                        </button>
                    </div>
                </div>

                <div className="card border-danger mb-4">
                    <div className="card-body text-danger">
                        <h5 className="card-title">Delete Transaction</h5>
                        <p>This action cannot be undone.</p>
                        <button
                            className="btn btn-danger mt-2"
                            onClick={openDeleteModal}
                        >
                            Delete Transaction
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
                                    transaction? This action cannot be undone.
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
                                    onClick={handleDeleteTransaction}
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
