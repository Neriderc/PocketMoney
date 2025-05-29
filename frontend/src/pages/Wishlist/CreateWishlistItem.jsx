import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";

export default function CreateWishlistItemPage() {
    const { householdId, childId, wishlistId } = useParams();
    const navigate = useNavigate();
    const [description, setDescription] = useState("");
    const [amount, setAmount] = useState("");
    const [priority, setPriority] = useState("");

    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isFormDirty, setIsFormDirty] = useState(false);
    const { apiFetch } = useAppContext();

    useEffect(() => {
        const isDirty = amount !== "" || description !== "" || priority !== "";
        setIsFormDirty(isDirty);
    }, [amount, description, priority]);

    const handleSubmit = (event) => {
        event.preventDefault();
        setIsSubmitting(true);
        apiFetch(`wishlists/${wishlistId}/wishlist_items`, {
            method: "POST",
            headers: {
                "Content-Type": "application/ld+json",
            },
            body: JSON.stringify({
                amount: parseFloat(amount),
                priority: parseFloat(priority),
                description: description,
            }),
        })
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                navigate(`/household/${householdId}/child/${childId}`);
            })
            .catch((error) => {
                console.error("Error creating wishlist item:", error);
                setIsSubmitting(false);
            });
    };

    const handleBack = () => {
        if (isFormDirty) {
            const confirmNavigation = window.confirm(
                "You have unsaved changes. Are you sure you want to leave?",
            );
            if (!confirmNavigation) {
                return;
            }
        }
        navigate(`/household/${householdId}/child/${childId}`);
    };

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
                                &larr; Back to child
                            </button>
                        </div>
                        <h3 className="card-title text-primary mb-4">
                            Add wishlist item
                        </h3>
                        <form onSubmit={handleSubmit}>
                            <div className="mb-3">
                                <label
                                    htmlFor="shortDescription"
                                    className="form-label"
                                >
                                    Description
                                </label>
                                <input
                                    type="text"
                                    className="form-control"
                                    id="shortDescription"
                                    value={description}
                                    onChange={(e) =>
                                        setDescription(e.target.value)
                                    }
                                    required
                                />
                            </div>
                            <div className="mb-3">
                                <label htmlFor="amount" className="form-label">
                                    Amount
                                </label>
                                <input
                                    type="number"
                                    className="form-control"
                                    id="amount"
                                    value={amount}
                                    onChange={(e) => setAmount(e.target.value)}
                                    required
                                />
                            </div>
                            <div className="mb-3">
                                <label htmlFor="amount" className="form-label">
                                    Priority
                                </label>
                                <input
                                    type="number"
                                    className="form-control"
                                    id="priority"
                                    value={priority}
                                    onChange={(e) =>
                                        setPriority(e.target.value)
                                    }
                                    required
                                />
                            </div>
                            <button
                                type="submit"
                                className="btn btn-primary"
                                disabled={isSubmitting}
                            >
                                {isSubmitting ? "Creating..." : "Create"}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}
