import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { getTextColourFromBrightness } from "../../utils/utils.js";
import { useAppContext } from "../../context/AppContext.jsx";

export default function AccountTransactionsPage() {
    const { householdId, childId, accountId } = useParams();
    const navigate = useNavigate();
    const [transactions, setTransactions] = useState([]);
    const [account, setAccount] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [itemsPerPage, setItemsPerPage] = useState(10);
    const [totalItems, setTotalItems] = useState(0);
    const [isLoading, setIsLoading] = useState(true);
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        apiFetch(`children/${childId}/accounts/${accountId}`)
            .then((response) => response.json())
            .then((data) => setAccount(data))
            .catch((error) =>
                console.error("Error fetching account details:", error),
            );

        apiFetch(
            `accounts/${accountId}/transactions?page=${currentPage}&itemsPerPage=${itemsPerPage}`,
        )
            .then((response) => response.json())
            .then((data) => {
                setTransactions(data["member"]);
                setTotalItems(data["totalItems"]);
                setIsLoading(false);
            })
            .catch((error) => {
                console.error("Error fetching transactions:", error);
                setIsLoading(false);
            });
    }, [accountId, currentPage, itemsPerPage, navigate]);

    const handlePageChange = (newPage) => setCurrentPage(newPage);
    const handleItemsPerPageChange = (event) => {
        setItemsPerPage(parseInt(event.target.value, 10));
        setCurrentPage(1);
    };
    const handleBackToChild = () =>
        navigate(`/household/${householdId}/child/${childId}`);
    const handleCreateTransaction = () =>
        navigate(
            `/household/${householdId}/child/${childId}/account/${accountId}/transaction/add`,
        );
    const handleEditAccount = () =>
        navigate(
            `/household/${householdId}/child/${childId}/account/${accountId}/edit`,
        );
    const handleViewTransaction = (transactionId) =>
        navigate(
            `/household/${householdId}/child/${childId}/account/${accountId}/transaction/${transactionId}`,
        );

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
                                onClick={handleEditAccount}
                                className="btn btn-outline-secondary"
                            >
                                Edit Account
                            </button>
                        </div>

                        {account && (
                            <div className="mb-4">
                                <div
                                    className="d-flex align-items-center mb-3"
                                    style={{
                                        backgroundColor:
                                            account.color || "white",
                                        padding: "10px",
                                        borderRadius: "5px",
                                        border: "1px solid #DDD",
                                    }}
                                >
                                    <i
                                        className={`bi ${account.icon} me-2`}
                                        style={{
                                            fontSize: "2rem",
                                            color: getTextColourFromBrightness(
                                                account.color || "#FFFFFF",
                                            ),
                                        }}
                                    ></i>
                                    <h3
                                        className="card-title"
                                        style={{
                                            color: getTextColourFromBrightness(
                                                account.color || "#FFFFFF",
                                            ),
                                        }}
                                    >
                                        {account.name}
                                    </h3>
                                </div>
                                <p className="card-text text-secondary">
                                    Balance:{" "}
                                    <strong className="text-dark">
                                        ${account.balance?.toFixed(2)}
                                    </strong>
                                </p>
                                <p className="card-text text-secondary">
                                    Created:{" "}
                                    <strong className="text-dark">
                                        {new Date(
                                            account.createdAt,
                                        ).toLocaleString()}
                                    </strong>
                                </p>
                                <p className="card-text text-secondary">
                                    Last Updated:{" "}
                                    <strong className="text-dark">
                                        {new Date(
                                            account.updatedAt,
                                        ).toLocaleString()}
                                    </strong>
                                </p>
                            </div>
                        )}

                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <h3 className="card-title text-primary">
                                Transactions
                            </h3>
                            <button
                                onClick={handleCreateTransaction}
                                className="btn btn-success"
                            >
                                + Add Transaction
                            </button>
                        </div>

                        <div className="mt-3">
                            <table
                                className="table table-striped"
                                style={{ tableLayout: "fixed", width: "100%" }}
                            >
                                <thead>
                                    <tr>
                                        <th style={{ width: "120px" }}>Date</th>
                                        <th className="d-none d-md-table-cell">
                                            Description
                                        </th>
                                        <th style={{ width: "100px" }}>
                                            Amount
                                        </th>
                                        <th className="d-none d-md-table-cell">
                                            Comment
                                        </th>
                                        <th style={{ width: "100px" }}>
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {transactions.length > 0 ? (
                                        transactions.map((transaction) => (
                                            <tr key={transaction["@id"]}>
                                                <td style={{ width: "120px" }}>
                                                    {new Date(
                                                        transaction.transactionDate,
                                                    ).toLocaleDateString()}
                                                </td>

                                                <td className="d-none d-md-table-cell">
                                                    <span
                                                        title={
                                                            transaction.description
                                                        }
                                                        style={{
                                                            display:
                                                                "inline-block",
                                                            whiteSpace:
                                                                "nowrap",
                                                            overflow: "hidden",
                                                            textOverflow:
                                                                "ellipsis",
                                                            width: "100%",
                                                        }}
                                                    >
                                                        {
                                                            transaction.description
                                                        }
                                                    </span>
                                                </td>

                                                <td style={{ width: "100px" }}>
                                                    $
                                                    {transaction.amount.toFixed(
                                                        2,
                                                    )}
                                                </td>

                                                <td className="d-none d-md-table-cell">
                                                    <span
                                                        title={
                                                            transaction.comment
                                                        }
                                                        style={{
                                                            display:
                                                                "inline-block",
                                                            whiteSpace:
                                                                "nowrap",
                                                            overflow: "hidden",
                                                            textOverflow:
                                                                "ellipsis",
                                                            width: "100%",
                                                        }}
                                                    >
                                                        {transaction.comment}
                                                    </span>
                                                </td>

                                                <td style={{ width: "100px" }}>
                                                    <button
                                                        onClick={() =>
                                                            handleViewTransaction(
                                                                transaction.id,
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
                                                No transactions available
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        <div className="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <span>Items per page: </span>
                                <select
                                    value={itemsPerPage}
                                    onChange={handleItemsPerPageChange}
                                    className="form-select d-inline-block w-auto"
                                >
                                    <option value={5}>5</option>
                                    <option value={10}>10</option>
                                    <option value={20}>20</option>
                                    <option value={50}>50</option>
                                    <option value={100}>100</option>
                                </select>
                            </div>
                            <div>
                                <button
                                    onClick={() =>
                                        handlePageChange(currentPage - 1)
                                    }
                                    disabled={currentPage === 1}
                                    className="btn btn-secondary me-2"
                                >
                                    Previous
                                </button>
                                <span>
                                    Page {currentPage} of{" "}
                                    {Math.ceil(totalItems / itemsPerPage)}
                                </span>
                                <button
                                    onClick={() =>
                                        handlePageChange(currentPage + 1)
                                    }
                                    disabled={
                                        currentPage >=
                                        Math.ceil(totalItems / itemsPerPage)
                                    }
                                    className="btn btn-secondary ms-2"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
