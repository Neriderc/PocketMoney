import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import Select from "react-select";
import { useAppContext } from "../../context/AppContext.jsx";

export default function EditScheduledTransactionPage() {
    const { householdId, childId, scheduledTransactionId } = useParams();
    const navigate = useNavigate();
    const [amount, setAmount] = useState("");
    const [shortDescription, setShortDescription] = useState("");
    const [nextTransactionDate, setNextTransactionDate] = useState("");
    const [repeatFrequency, setRepeatFrequency] = useState("weekly");
    const [comment, setComment] = useState("");
    const [selectedAccounts, setSelectedAccounts] = useState([]);
    const [amountCalculation, setAmountCalculation] = useState("fixed");
    const [accounts, setAccounts] = useState([]);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("");
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        apiFetch(`children/${childId}/accounts`)
            .then((response) => response.json())
            .then((data) => {
                const child = data["member"]?.[0];
                setAccounts(child?.accounts || []);
            })
            .catch((error) => {
                console.error("Error fetching accounts:", error);
                setMessage("Failed to load accounts.");
                setMessageType("danger");
            });

        apiFetch(
            `children/${childId}/scheduled_transactions/${scheduledTransactionId}`,
        )
            .then((response) => response.json())
            .then((data) => {
                setAmount(data.amount);
                setShortDescription(data.description);
                setNextTransactionDate(data.nextExecutionDate.slice(0, 10));
                setRepeatFrequency(data.repeatFrequency);
                setComment(data.comment);
                setAmountCalculation(data.amountBase);
                setSelectedAccounts(
                    data.accounts.map((account) => account["@id"]),
                );
            })
            .catch((error) => {
                console.error("Error fetching transaction:", error);
                setMessage("Failed to load transaction.");
                setMessageType("danger");
            });
    }, [childId, scheduledTransactionId]);

    const handleSubmit = (event) => {
        event.preventDefault();
        setIsSubmitting(true);
        setMessage("");
        setMessageType("");

        apiFetch(
            `children/${childId}/scheduled_transactions/${scheduledTransactionId}`,
            {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/merge-patch+json",
                },
                body: JSON.stringify({
                    amount: parseFloat(amount),
                    description: shortDescription,
                    nextExecutionDate: nextTransactionDate,
                    repeatFrequency,
                    comment,
                    amountCalculation,
                    accounts: selectedAccounts,
                }),
            },
        )
            .then(async (response) => {
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(
                        errorData["description"] || "Update failed.",
                    );
                }
                return response.json();
            })
            .then(() => {
                navigate(`/household/${householdId}/child/${childId}`);
            })
            .catch((error) => {
                console.error("Error updating transaction:", error);
                setMessage(error.message || "An error occurred.");
                setMessageType("danger");
                setIsSubmitting(false);
            });
    };

    const handleDelete = () => {
        apiFetch(
            `children/${childId}/scheduled_transactions/${scheduledTransactionId}`,
            {
                method: "DELETE",
            },
        )
            .then((response) => {
                if (response.ok) {
                    navigate(`/household/${householdId}/child/${childId}`);
                } else {
                    setMessage("Error deleting scheduled transaction.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error deleting scheduled transaction.");
                setMessageType("danger");
            });
    };

    const openDeleteModal = () => setShowDeleteModal(true);
    const closeDeleteModal = () => setShowDeleteModal(false);

    const accountOptions = accounts.map((account) => ({
        value: account["@id"],
        label: account.name,
    }));

    const selectedAccountOptions = accountOptions.filter((opt) =>
        selectedAccounts.includes(opt.value),
    );

    return (
        <div>
            <NavBar />
            <div className="container mt-5">
                {message && (
                    <div className={`alert alert-${messageType}`} role="alert">
                        {message}
                    </div>
                )}
                <div
                    className="card shadow-sm border-0 mb-4"
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <div className="card-body">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <button
                                onClick={() =>
                                    navigate(
                                        `/household/${householdId}/child/${childId}`,
                                    )
                                }
                                className="btn btn-secondary"
                            >
                                &larr; Back to Child
                            </button>
                        </div>
                        <h3 className="card-title text-primary mb-4">
                            Edit Scheduled Transaction
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
                                    onChange={(e) => setAmount(e.target.value)}
                                    required
                                />
                            </div>
                            <div className="mb-3">
                                <label
                                    htmlFor="amountCalculation"
                                    className="form-label"
                                >
                                    Amount Calculation
                                </label>
                                <select
                                    className="form-select"
                                    id="amountCalculation"
                                    value={amountCalculation}
                                    onChange={(e) =>
                                        setAmountCalculation(e.target.value)
                                    }
                                >
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="age">
                                        Based on Child&apos;s Age
                                    </option>
                                </select>
                            </div>
                            <div className="mb-3">
                                <label className="form-label">
                                    Split Between Accounts
                                </label>
                                <Select
                                    isMulti
                                    options={accountOptions}
                                    value={selectedAccountOptions}
                                    onChange={(selectedOptions) =>
                                        setSelectedAccounts(
                                            selectedOptions
                                                ? selectedOptions.map(
                                                      (opt) => opt.value,
                                                  )
                                                : [],
                                        )
                                    }
                                    classNamePrefix="react-select"
                                />
                            </div>
                            <div className="mb-3">
                                <label
                                    htmlFor="nextTransactionDate"
                                    className="form-label"
                                >
                                    Next Transaction Date
                                </label>
                                <input
                                    type="date"
                                    className="form-control"
                                    id="nextTransactionDate"
                                    value={nextTransactionDate}
                                    onChange={(e) =>
                                        setNextTransactionDate(e.target.value)
                                    }
                                    required
                                />
                            </div>
                            <div className="mb-3">
                                <label
                                    htmlFor="repeatFrequency"
                                    className="form-label"
                                >
                                    Repeat
                                </label>
                                <select
                                    className="form-select"
                                    id="repeatFrequency"
                                    value={repeatFrequency}
                                    onChange={(e) =>
                                        setRepeatFrequency(e.target.value)
                                    }
                                    required
                                >
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
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
                                {isSubmitting ? "Updating..." : "Update"}
                            </button>
                        </form>
                    </div>
                </div>

                <div className="card border-danger mb-4">
                    <div className="card-body text-danger">
                        <h5 className="card-title">
                            Delete Scheduled Transaction
                        </h5>
                        <p>This action cannot be undone.</p>
                        <button
                            className="btn btn-danger"
                            onClick={openDeleteModal}
                        >
                            Delete Scheduled Transaction
                        </button>
                    </div>
                </div>

                {showDeleteModal && (
                    <div
                        className="modal show fade d-block"
                        tabIndex="-1"
                        role="dialog"
                    >
                        <div className="modal-dialog" role="document">
                            <div className="modal-content">
                                <div className="modal-header">
                                    <h5 className="modal-title">
                                        Confirm Delete
                                    </h5>
                                    <button
                                        type="button"
                                        className="btn-close"
                                        onClick={closeDeleteModal}
                                    />
                                </div>
                                <div className="modal-body">
                                    <p>
                                        Are you sure you want to delete this
                                        scheduled transaction?
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
                                        onClick={handleDelete}
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
