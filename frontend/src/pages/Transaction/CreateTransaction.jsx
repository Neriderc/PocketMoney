import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";
import { getLocalDateString } from "../../utils/utils";

export default function CreateTransactionPage() {
    const { householdId, childId, accountId } = useParams();
    const navigate = useNavigate();
    const [amount, setAmount] = useState("");
    const [transactionType, setTransactionType] = useState("purchase");
    const [shortDescription, setShortDescription] = useState("");
    const [transactionDate, setTransactionDate] = useState(
        getLocalDateString()
    );
    const [comment, setComment] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isFormDirty, setIsFormDirty] = useState(false);
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        const isDirty =
            amount !== "" ||
            shortDescription !== "" ||
            transactionDate !== getLocalDateString() ||
            comment !== "";
        setIsFormDirty(isDirty);
    }, [amount, shortDescription, transactionDate, comment]);

    const handleSubmit = (event) => {
        event.preventDefault();
        setIsSubmitting(true);
        apiFetch(`accounts/${accountId}/transactions`, {
            method: "POST",
            headers: {
                "Content-Type": "application/ld+json",
            },
            body: JSON.stringify({
                amount: parseFloat(amount),
                description: shortDescription,
                transactionDate,
                comment,
                account: `/api/children/${childId}/accounts/${accountId}`,
            }),
        })
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                navigate(
                    `/household/${householdId}/child/${childId}/account/${accountId}`,
                );
            })
            .catch((error) => {
                console.error("Error creating transaction:", error);
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
        navigate(
            `/household/${householdId}/child/${childId}/account/${accountId}`,
        );
    };

    function updateAmount(value, type = transactionType) {
        let amount = Math.abs(parseFloat(value) || 0);
        if (type === "purchase") {
            amount *= -1;
        }
        setAmount(amount.toString());
    }

    function handleTransactionTypeChange(value) {
        setTransactionType(value);
        // Make sure amount is positive/negative based on type selection
        updateAmount(document.getElementById("amount").value, value);
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
                                &larr; Back to Account
                            </button>
                        </div>
                        <h3 className="card-title text-primary mb-4">
                            Add Transaction
                        </h3>
                        <form onSubmit={handleSubmit}>
                            <div className="mb-3">
                                <label
                                    htmlFor="shortDescription"
                                    className="form-label"
                                >
                                    Short Description
                                </label>
                                <input
                                    type="text"
                                    className="form-control"
                                    id="shortDescription"
                                    value={shortDescription}
                                    onChange={(e) =>
                                        setShortDescription(e.target.value)
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
                                    onChange={(e) =>
                                        updateAmount(e.target.value)
                                    }
                                    required
                                />
                            </div>
                            <div className="mb-3">
                                <label className="form-label d-block">
                                    Transaction Type
                                </label>
                                <div
                                    className="btn-group"
                                    role="group"
                                    aria-label="Transaction type toggle"
                                >
                                    <button
                                        type="button"
                                        className={`btn ${transactionType === "deposit" ? "btn-success" : "btn-outline-success"}`}
                                        onClick={() =>
                                            handleTransactionTypeChange(
                                                "deposit",
                                            )
                                        }
                                    >
                                        Deposit
                                    </button>
                                    <button
                                        type="button"
                                        className={`btn ${transactionType === "purchase" ? "btn-danger" : "btn-outline-danger"}`}
                                        onClick={() =>
                                            handleTransactionTypeChange(
                                                "purchase",
                                            )
                                        }
                                    >
                                        Purchase
                                    </button>
                                </div>
                            </div>
                            <div className="mb-3">
                                <label
                                    htmlFor="transactionDate"
                                    className="form-label"
                                >
                                    Transaction Date
                                </label>
                                <input
                                    type="date"
                                    className="form-control"
                                    id="transactionDate"
                                    value={transactionDate}
                                    onChange={(e) =>
                                        setTransactionDate(e.target.value)
                                    }
                                    required
                                />
                            </div>
                            <div className="mb-3">
                                <label htmlFor="comment" className="form-label">
                                    Comment
                                </label>
                                <textarea
                                    className="form-control"
                                    id="comment"
                                    value={comment}
                                    onChange={(e) => setComment(e.target.value)}
                                    rows={4}
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
