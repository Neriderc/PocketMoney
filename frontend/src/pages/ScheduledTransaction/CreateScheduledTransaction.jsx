import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import Select from "react-select";
import { useAppContext } from "../../context/AppContext.jsx";

export default function CreateScheduledTransactionPage() {
    const { householdId, childId } = useParams();
    const navigate = useNavigate();
    const [amount, setAmount] = useState("");
    const [shortDescription, setShortDescription] = useState("");
    const [nextTransactionDate, setNextTransactionDate] = useState(
        new Date().toISOString().split("T")[0],
    );
    const [repeatFrequency, setRepeatFrequency] = useState("weekly");
    const [comment, setComment] = useState("");
    const [selectedAccounts, setSelectedAccounts] = useState([]);
    const [amountCalculation, setAmountCalculation] = useState("fixed");
    const [accounts, setAccounts] = useState([]);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isFormDirty, setIsFormDirty] = useState(false);
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("");
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
    }, [childId]);

    useEffect(() => {
        const isDirty =
            amount !== "" ||
            shortDescription !== "" ||
            nextTransactionDate !== new Date().toISOString().split("T")[0] ||
            comment !== "";
        setIsFormDirty(isDirty);
    }, [amount, shortDescription, nextTransactionDate, comment]);

    const handleSubmit = (event) => {
        event.preventDefault();
        setIsSubmitting(true);
        setMessage("");
        setMessageType("");

        apiFetch(`children/${childId}/scheduled_transactions`, {
            method: "POST",
            headers: {
                "Content-Type": "application/ld+json",
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
        })
            .then(async (response) => {
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(
                        errorData["description"] ||
                            "Failed to create transaction.",
                    );
                }
                return response.json();
            })
            .then(() => {
                navigate(`/household/${householdId}/child/${childId}`);
            })
            .catch((error) => {
                console.error("Error creating scheduled transaction:", error);
                setMessage(error.message || "An error occurred.");
                setMessageType("danger");
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
                {message && (
                    <div className={`alert alert-${messageType}`} role="alert">
                        {message}
                    </div>
                )}
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
                                &larr; Back to Child
                            </button>
                        </div>
                        <h3 className="card-title text-primary mb-4">
                            Add Scheduled Transaction
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
                                    options={accounts.map((account) => ({
                                        value: account["@id"],
                                        label: account.name,
                                    }))}
                                    value={accounts
                                        .filter((account) =>
                                            selectedAccounts.includes(
                                                account["@id"],
                                            ),
                                        )
                                        .map((account) => ({
                                            value: account["@id"],
                                            label: account.name,
                                        }))}
                                    onChange={(selectedOptions) => {
                                        setSelectedAccounts(
                                            selectedOptions.map(
                                                (opt) => opt.value,
                                            ),
                                        );
                                    }}
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
                                {isSubmitting ? "Creating..." : "Create"}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}
