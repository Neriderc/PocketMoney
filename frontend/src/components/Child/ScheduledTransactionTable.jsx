import React, { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useAppContext } from "../../context/AppContext.jsx";

export default function ScheduledTransactionTable() {
    const { householdId, childId } = useParams();
    const navigate = useNavigate();
    const [transactionSchedules, setTransactionSchedules] = useState([]);
    const { apiFetch } = useAppContext();

    useEffect(() => {
        apiFetch(`children/${childId}/scheduled_transactions`)
            .then((response) => response.json())
            .then((data) => setTransactionSchedules(data["member"]))
            .catch((error) =>
                console.error("Error fetching transaction schedules:", error),
            );
    }, [childId]);
    const handleCreateScheduledTransaction = () => {
        navigate(
            `/household/${householdId}/child/${childId}/scheduled_transaction/add`,
        );
    };

    function handleViewScheduledTransaction(scheduleId) {
        navigate(
            `/household/${householdId}/child/${childId}/scheduled_transaction/${scheduleId}`,
        );
    }

    return (
        <div
            className="card shadow-sm border-0 mb-4"
            style={{ backgroundColor: "#f8f9fa" }}
        >
            <div className="card-body">
                <div className="d-flex justify-content-between align-items-center mb-3">
                    <h3 className="card-title text-primary">
                        Scheduled transactions
                    </h3>
                    <button
                        onClick={handleCreateScheduledTransaction}
                        className="btn btn-success"
                    >
                        + Add scheduled transaction
                    </button>
                </div>

                <div className="mt-3">
                    <table className="table table-striped">
                        <thead>
                            <tr>
                                <th>Next Execution</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Comment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {transactionSchedules.length > 0 ? (
                                transactionSchedules.map((schedule) => (
                                    <tr key={schedule["@id"]}>
                                        <td>
                                            {new Date(
                                                schedule.nextExecutionDate,
                                            ).toLocaleDateString()}
                                        </td>
                                        <td>{schedule.description}</td>
                                        <td>${schedule.amount.toFixed(2)}</td>
                                        <td>{schedule.comment}</td>
                                        <td style={{ width: "100px" }}>
                                            <button
                                                onClick={() =>
                                                    handleViewScheduledTransaction(
                                                        schedule.id,
                                                    )
                                                }
                                                className="btn btn-primary btn-sm"
                                            >
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td
                                        colSpan="5"
                                        className="text-center text-muted"
                                    >
                                        No scheduled transactions available
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}
