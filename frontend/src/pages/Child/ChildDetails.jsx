import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { getTextColourFromBrightness } from "../../utils/utils.js";
import { useAppContext } from "../../context/AppContext.jsx";
import WishlistItemTable from "../../components/Child/WishlistItemTable.jsx";

export default function ChildPage() {
    const { householdId, childId } = useParams();
    const navigate = useNavigate();
    const [child, setChild] = useState(null);
    const [accounts, setAccounts] = useState([]);
    const [wishlist, setWishlist] = useState([]);
    const [savingsGoal, setSavingsGoal] = useState(null);
    const [savingsGoalLink, setSavingsGoalLink] = useState(null);
    const [cantBuyBeforeDate, setCantBuyBeforeDate] = useState(null);
    const { apiFetch } = useAppContext();

    useEffect(() => {
        apiFetch(`children/${childId}`)
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                setChild(data);
            })
            .catch((error) =>
                console.error("Error fetching child data:", error),
            );

        apiFetch(`children/${childId}/accounts`)
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                if (data.member && Array.isArray(data.member)) {
                    const accounts = data.member.flatMap(
                        (item) => item.accounts || [],
                    );
                    setAccounts(accounts);
                }
            })
            .catch((error) =>
                console.error("Error fetching accounts data:", error),
            );

        apiFetch(`children/${childId}/wishlists`)
            .then((response) => response.json())
            .then((data) => {
                if (data.member && Array.isArray(data.member)) {
                    setWishlist(data.member[0]);
                    setCantBuyBeforeDate(
                        new Date(
                            data.member[0].cantBuyBeforeDate,
                        ).toLocaleDateString(),
                    );
                    setWishlist(data.member);
                    const parts = data.member[0].currentlySavingFor.split("/");
                    const wishlistItemId = parts.pop();

                    setSavingsGoalLink(
                        `/household/${householdId}/child/${childId}/wishlist/${data.member[0].id}/wishlist_item/${wishlistItemId}`,
                    );
                    const savingItemUri =
                        data.member[0].currentlySavingFor.substring(5);
                    apiFetch(savingItemUri)
                        .then((response) => response.json())
                        .then((data) => {
                            setSavingsGoal(data.description);
                        })
                        .catch((error) =>
                            console.error(
                                "Error fetching wishlist items:",
                                error,
                            ),
                        );
                }
            })
            .catch((error) =>
                console.error("Error fetching wishlist data:", error),
            );
    }, [childId, navigate]);

    if (!child) {
        return (
            <div>
                <NavBar />
            </div>
        );
    }

    const totalBalance = accounts.reduce(
        (sum, account) => sum + (account.balance ?? 0),
        0,
    );

    const handleAccountClick = (childId, fullAccountId) => {
        const accountId = fullAccountId.split("/").pop();
        navigate(
            `/household/${householdId}/child/${childId}/account/${accountId}`,
        );
    };

    const handleBackToDashboard = () => {
        navigate("/dashboard");
    };

    const handleEditChild = () => {
        navigate(`/household/${householdId}/child/${childId}/edit`);
    };

    const handleEditGoal = () => {
        navigate(`/household/${householdId}/child/${childId}/wishlist/edit`);
    };

    const handleCreateTransaction = (childId, accountId) => {
        navigate(
            `/household/${householdId}/child/${childId}/account/${accountId}/transaction/add`,
        );
    };

    return (
        <div>
            <NavBar />
            <div className="container mt-5">
                <div
                    className="card shadow-sm border-0 mb-4 "
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <div className="card-body">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <button
                                onClick={handleBackToDashboard}
                                className="btn btn-secondary mb-3"
                            >
                                &larr; Back to Dashboard
                            </button>
                            <button
                                onClick={handleEditChild}
                                className="btn btn-outline-secondary mb-3"
                            >
                                Edit Child
                            </button>
                        </div>
                        <h3 className="card-title text-primary">
                            {child.name}
                        </h3>
                        <p className="card-text text-secondary">
                            Total Balance:{" "}
                            <strong className="text-dark">
                                ${totalBalance.toFixed(2)}
                            </strong>
                        </p>
                    </div>
                </div>
                <div
                    className="card shadow-sm border-0 mb-4 "
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <div className="card-body">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <h5 className="text-secondary">Saving goal</h5>
                            <button
                                onClick={handleEditGoal}
                                className="btn btn-outline-secondary mb-3"
                            >
                                Edit goal
                            </button>
                        </div>
                        <p className="card-text text-secondary">
                            Currently saving for:{" "}
                            <a href={savingsGoalLink}>
                                <strong className="text-dark">
                                    {savingsGoal}
                                </strong>
                            </a>
                        </p>
                        <p className="card-text text-secondary">
                            Can't buy before:{" "}
                            <strong className="text-dark">
                                {cantBuyBeforeDate}
                            </strong>
                        </p>
                    </div>
                </div>
                <div
                    className="card shadow-sm border-0 mb-4 "
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <div className="card-body">
                        <div className="mt-3">
                            <h5 className="text-secondary">Accounts</h5>
                            <ul className="list-unstyled">
                                {accounts.length > 0 ? (
                                    accounts.map((account) => {
                                        const backgroundColor =
                                            account.color || "#ffffff";
                                        const textColor =
                                            getTextColourFromBrightness(
                                                backgroundColor,
                                            );

                                        return (
                                            <li
                                                key={account["@id"]}
                                                className="mb-2 d-flex justify-content-between align-items-center"
                                                style={{
                                                    cursor: "pointer",
                                                    border: "1px solid #ddd",
                                                    padding: "10px",
                                                    borderRadius: "5px",
                                                    backgroundColor:
                                                        backgroundColor,
                                                    color: textColor,
                                                }}
                                                onClick={() =>
                                                    handleAccountClick(
                                                        child.id,
                                                        account["@id"],
                                                    )
                                                }
                                            >
                                                <div className="d-flex align-items-center">
                                                    {account.icon && (
                                                        <i
                                                            className={`bi ${account.icon} me-2`}
                                                        ></i>
                                                    )}
                                                    <span>{account.name}:</span>{" "}
                                                    <span>
                                                        $
                                                        {account.balance?.toFixed(
                                                            2,
                                                        ) ?? "N/A"}
                                                    </span>
                                                </div>
                                                <button
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        handleCreateTransaction(
                                                            child.id,
                                                            account["@id"]
                                                                .split("/")
                                                                .pop(),
                                                        );
                                                    }}
                                                    className="btn btn-success btn-sm"
                                                >
                                                    + Add Transaction
                                                </button>
                                            </li>
                                        );
                                    })
                                ) : (
                                    <li className="mb-2 text-muted">
                                        No accounts available
                                    </li>
                                )}
                            </ul>
                        </div>
                    </div>
                </div>
                <div
                    className="card shadow-sm border-0 mb-4 "
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <WishlistItemTable />
                </div>
            </div>
        </div>
    );
}
