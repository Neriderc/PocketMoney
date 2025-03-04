import React, { useEffect, useState } from "react";
import { useParams, useNavigate, Link } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";

export default function TransactionDetailsPage() {
    const { householdId, childId, accountId, transactionId } = useParams();
    const navigate = useNavigate();
    const [transaction, setTransaction] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        apiFetch(
            `accounts/${accountId}/transactions/${transactionId}`,
            logout,
            {
                method: "GET",
            },
        )
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                setTransaction(data);
                setIsLoading(false);
            })
            .catch((error) => {
                console.error("Error fetching transaction details:", error);
                setIsLoading(false);
            });
    }, [transactionId, navigate]);

    const handleBackToAccountTransactions = () => {
        navigate(
            `/household/${householdId}/child/${childId}/account/${accountId}`,
        );
    };
    const handleEditTransactions = () => {
        navigate(
            `/household/${householdId}/child/${childId}/account/${accountId}/transaction/${transactionId}/edit`,
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

    if (!transaction) {
        return (
            <div>
                <NavBar />
                <div className="container mt-5">
                    <div className="alert alert-danger" role="alert">
                        Transaction not found.
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
                                onClick={handleBackToAccountTransactions}
                                className="btn btn-secondary"
                            >
                                &larr; Back to Account Transactions
                            </button>
                            <button
                                onClick={handleEditTransactions}
                                className="btn btn-outline-secondary"
                            >
                                Edit Transaction
                            </button>
                        </div>
                        <h3 className="card-title text-primary">
                            Transaction Details
                        </h3>
                        <div className="mt-3">
                            <div className="mb-3">
                                <strong>Date:</strong>{" "}
                                {new Date(
                                    transaction.transactionDate,
                                ).toLocaleDateString()}
                            </div>
                            <div className="mb-3">
                                <strong>Description:</strong>{" "}
                                {transaction.description}
                            </div>
                            <div className="mb-3">
                                <strong>Amount:</strong> $
                                {transaction.amount?.toFixed(2)}
                            </div>
                            <div className="mb-3">
                                <strong>Comment:</strong> {transaction.comment}
                            </div>
                            <div className="mb-3">
                                <strong>Created At:</strong>{" "}
                                {new Date(
                                    transaction.createdAt,
                                ).toLocaleString()}
                            </div>
                            <div className="mb-3">
                                <strong>Updated At:</strong>{" "}
                                {new Date(
                                    transaction.updatedAt,
                                ).toLocaleString()}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
