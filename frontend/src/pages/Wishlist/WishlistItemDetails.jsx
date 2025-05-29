import React, { useEffect, useState } from "react";
import { useParams, useNavigate, Link } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";

export default function WishlistItemDetailsPage() {
    const { householdId, childId, wishlistId, wishlistItemId } = useParams();
    const navigate = useNavigate();
    const [wishlistItem, setWishlistItem] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        apiFetch(`wishlists/${wishlistId}/wishlist_items/${wishlistItemId}`, {
            method: "GET",
        })
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                setWishlistItem(data);
                setIsLoading(false);
            })
            .catch((error) => {
                console.error("Error fetching wishlistItem details:", error);
                setIsLoading(false);
            });
    }, [wishlistId, wishlistItemId]);

    const handleBackToChild = () => {
        navigate(`/household/${householdId}/child/${childId}`);
    };
    const handleEditTransactions = () => {
        navigate(
            `/household/${householdId}/child/${childId}/wishlist/${wishlistId}/wishlist_item/${wishlistItemId}/edit`,
        );
    };

    if (isLoading) {
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

    if (!wishlistItem) {
        return (
            <div>
                <NavBar />
                <div className="container mt-5">
                    <div className="alert alert-danger" role="alert">
                        Wishlist item not found.
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
                                onClick={handleBackToChild}
                                className="btn btn-secondary"
                            >
                                &larr; Back to child details
                            </button>
                            <button
                                onClick={handleEditTransactions}
                                className="btn btn-outline-secondary"
                            >
                                Edit wishlist item
                            </button>
                        </div>
                        <h3 className="card-title text-primary">
                            Wishlist item details
                        </h3>
                        <div className="mt-3">
                            <div className="mb-3">
                                <strong>Description:</strong>{" "}
                                {wishlistItem.description}
                            </div>
                            <div className="mb-3">
                                <strong>Amount:</strong> $
                                {wishlistItem.amount?.toFixed(2)}
                            </div>
                            <div className="mb-3">
                                <strong>Priority:</strong>{" "}
                                {wishlistItem.priority}
                            </div>
                            <div className="mb-3">
                                <strong>Created At:</strong>{" "}
                                {new Date(
                                    wishlistItem.createdAt,
                                ).toLocaleString()}
                            </div>
                            <div className="mb-3">
                                <strong>Updated At:</strong>{" "}
                                {new Date(
                                    wishlistItem.updatedAt,
                                ).toLocaleString()}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
