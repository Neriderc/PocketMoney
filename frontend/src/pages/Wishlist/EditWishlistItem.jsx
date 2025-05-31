import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";

export default function EditWishlistItemPage() {
    const { householdId, childId, wishlistId, wishlistItemId } = useParams();
    const navigate = useNavigate();
    const [wishlistItem, setWishlistItem] = useState(null);
    const [newDescription, setNewDescription] = useState("");
    const [newAmount, setNewAmount] = useState("");
    const [newPriority, setNewPriority] = useState("");
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("");
    const { apiFetch } = useAppContext();

    useEffect(() => {
        apiFetch(`wishlists/${wishlistId}/wishlist_items/${wishlistItemId}`)
            .then((response) => response.json())
            .then((data) => {
                setWishlistItem(data);
                setNewDescription(data.description);
                setNewAmount(data.amount);
                setNewPriority(data.priority || "");
            })
            .catch((error) =>
                console.error("Error fetching wishlist item data:", error),
            );
    }, [wishlistId, wishlistItemId]);

    const handleUpdateWishlistItem = () => {
        const payload = {
            description: newDescription,
            amount: parseFloat(newAmount),
            priority: parseFloat(newPriority),
        };

        apiFetch(`wishlists/${wishlistId}/wishlist_items/${wishlistItemId}`, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/merge-patch+json",
            },
            body: JSON.stringify(payload),
        })
            .then((response) => {
                if (response.ok) {
                    setMessage("Wishlist item updated successfully!");
                    setMessageType("success");
                } else {
                    setMessage("Error updating wishlist item.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error updating wishlist item.");
                setMessageType("danger");
            });
    };

    const handleDeleteWishlistItem = () => {
        apiFetch(`wishlists/${wishlistId}/wishlist_items/${wishlistItemId}`, {
            method: "DELETE",
        })
            .then((response) => {
                if (response.ok) {
                    navigate(`/household/${householdId}/child/${childId}`);
                } else {
                    setMessage("Error deleting wishlist item.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error deleting wishlist item.");
                setMessageType("danger");
            });
    };

    const openDeleteModal = () => setShowDeleteModal(true);
    const closeDeleteModal = () => setShowDeleteModal(false);

    if (!wishlistItem) {
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
                                    `/household/${householdId}/child/${childId}/wishlist/${wishlistId}/wishlist_item/${wishlistItemId}`,
                                )
                            }
                            className="btn btn-secondary mb-3"
                        >
                            &larr; Back to wishlist item
                        </button>
                        <h5 className="card-title">
                            Edit Wishlist Item Details
                        </h5>
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
                            <label>Priority</label>
                            <input
                                type="number"
                                className="form-control"
                                value={newPriority}
                                onChange={(e) => setNewPriority(e.target.value)}
                            />
                        </div>
                        <button
                            className="btn btn-primary mt-2"
                            onClick={handleUpdateWishlistItem}
                        >
                            Update Wishlist Item
                        </button>
                    </div>
                </div>

                <div className="card border-danger mb-4">
                    <div className="card-body text-danger">
                        <h5 className="card-title">Delete wishlist item</h5>
                        <p>This action cannot be undone.</p>
                        <button
                            className="btn btn-danger mt-2"
                            onClick={openDeleteModal}
                        >
                            Delete wishlist item
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
                                    wishlist item? This action cannot be undone.
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
                                    onClick={handleDeleteWishlistItem}
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
