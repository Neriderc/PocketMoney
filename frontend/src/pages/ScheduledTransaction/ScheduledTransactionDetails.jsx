import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";

export default function SchedulesTransactionDetailsPage() {
    const { householdId, childId, scheduledTransactionId } = useParams();
    const navigate = useNavigate();
    const [schedule, setSchedule] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        apiFetch(
            `children/${childId}/scheduled_transactions/${scheduledTransactionId}`,
            logout,
            {
                method: "GET",
            },
        )
            .then((response) => response.json())
            .then((data) => {
                setSchedule(data);
                setIsLoading(false);
            })
            .catch((error) => {
                console.error(
                    "Error fetching transaction schedule details:",
                    error,
                );
                setIsLoading(false);
            });
    }, [scheduledTransactionId]);

    const handleBackToChild = () => {
        navigate(`/household/${householdId}/child/${childId}`);
    };

    const handleEditSchedule = () => {
        navigate(
            `/household/${householdId}/child/${childId}/scheduled_transaction/${scheduledTransactionId}/edit`,
        );
    };

    if (isLoading) {
        return (
            <div>
                <NavBar />
                <div className="container mt-5 text-center">
                    <div className="spinner-border text-primary" role="status">
                        <span className="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        );
    }

    if (!schedule) {
        return (
            <div>
                <NavBar />
                <div className="container mt-5">
                    <div className="alert alert-danger" role="alert">
                        Transaction Schedule not found.
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
                                &larr; Back to Child
                            </button>
                            <button
                                onClick={handleEditSchedule}
                                className="btn btn-outline-secondary"
                            >
                                Edit Schedule
                            </button>
                        </div>
                        <h3 className="card-title text-primary">
                            Transaction Schedule Details
                        </h3>
                        <div className="mt-3">
                            <div className="mb-3">
                                <strong>Description:</strong>{" "}
                                {schedule.description}
                            </div>
                            <div className="mb-3">
                                <strong>Amount:</strong> $
                                {schedule.amount?.toFixed(2)}
                            </div>
                            <div className="mb-3">
                                <strong>Base:</strong> {schedule.amountBase}
                            </div>
                            <div className="mb-3">
                                <strong>Repeat:</strong>{" "}
                                {schedule.repeatFrequency}
                            </div>
                            <div className="mb-3">
                                <strong>Next Run:</strong>{" "}
                                {new Date(
                                    schedule.nextExecutionDate,
                                ).toLocaleDateString()}
                            </div>
                            <div className="mb-3">
                                <strong>Comment:</strong> {schedule.comment}
                            </div>
                            <div className="mb-3">
                                <strong>Created At:</strong>{" "}
                                {new Date(schedule.createdAt).toLocaleString()}
                            </div>
                            <div className="mb-3">
                                <strong>Updated At:</strong>{" "}
                                {new Date(schedule.updatedAt).toLocaleString()}
                            </div>
                            <div className="mb-3">
                                <strong>Accounts:</strong>
                                <ul>
                                    {schedule.accounts?.map((account) => (
                                        <li key={account["@id"] || account.id}>
                                            {account.name || account["@id"]}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
