import React, { useState, useEffect } from "react";
import Select from "react-select";
import { useParams, useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";

export default function EditSavingGoalPage() {
    const { householdId, childId } = useParams();
    const navigate = useNavigate();
    const [wishlist, setWishlist] = useState(null);
    const [wishlistItems, setWishlistItems] = useState([]);
    const [newCantBuyBeforeDate, setNewCantBuyBeforeDate] = useState("");
    const [newSavingsGoal, setNewSavingsGoal] = useState("");
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("");
    const { apiFetch } = useAppContext();

    useEffect(() => {
        apiFetch(`children/${childId}/wishlists`)
            .then((response) => response.json())
            .then((data) => {
                if (data.member && Array.isArray(data.member)) {
                    setWishlist(data.member[0]);
                    if (data.member[0].cantBuyBeforeDate) {
                        setNewCantBuyBeforeDate(
                            data.member[0].cantBuyBeforeDate.slice(0, 10),
                        );
                    } else {
                        setNewCantBuyBeforeDate("");
                    }
                    setNewSavingsGoal(data.member[0].currentlySavingFor);

                    apiFetch(`wishlists/${data.member[0].id}/wishlist_items`)
                        .then((response) => response.json())
                        .then((data) => {
                            setWishlistItems(data.member);
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
    }, [childId]);

    const wishlistItemOptions = wishlistItems.map((item) => ({
        value: item["@id"],
        label: item.description,
    }));

    const handleUpdateSavingsGoal = () => {
        const payload = {
            currentlySavingFor: newSavingsGoal,
            cantBuyBeforeDate: newCantBuyBeforeDate,
        };

        apiFetch(`children/${childId}/wishlists/${wishlist.id}`, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/merge-patch+json",
            },
            body: JSON.stringify(payload),
        })
            .then((response) => {
                if (response.ok) {
                    setMessage("Wishlist item updated successfully!");
                    setMessageType("success");
                } else {
                    setMessage("Error updating wishlist item.");
                    setMessageType("danger");
                }
            })
            .catch(() => {
                setMessage("Error updating wishlist item.");
                setMessageType("danger");
            });
    };

    if (!wishlist) {
        return (
            <div>
                <NavBar />
            </div>
        );
    }

    return (
        <div>
            <NavBar />
            <div className="container mt-5">
                {message && (
                    <div
                        className={`alert alert-${messageType} mt-3`}
                        role="alert"
                    >
                        {message}
                    </div>
                )}

                <div
                    className="card shadow-sm border-0 mb-4"
                    style={{ backgroundColor: "#f8f9fa" }}
                >
                    <div className="card-body">
                        <button
                            onClick={() =>
                                navigate(
                                    `/household/${householdId}/child/${childId}`,
                                )
                            }
                            className="btn btn-secondary mb-3"
                        >
                            &larr; Back to child
                        </button>
                        <h5 className="card-title">Edit savings goal</h5>
                        <div className="form-group">
                            <label>Savings goal</label>
                            <Select
                                className="basic-single"
                                classNamePrefix="select"
                                name="savingsGoal"
                                options={wishlistItemOptions}
                                onChange={(selected) => {
                                    setNewSavingsGoal(selected.value);
                                }}
                                value={
                                    wishlistItemOptions.find(
                                        (opt) => opt.value === newSavingsGoal,
                                    ) || null
                                }
                            />
                        </div>
                        <div className="form-group">
                            <label>Can't buy until this date</label>
                            <input
                                type="date"
                                className="form-control"
                                value={newCantBuyBeforeDate}
                                onChange={(e) =>
                                    setNewCantBuyBeforeDate(e.target.value)
                                }
                            />
                        </div>
                        <button
                            className="btn btn-primary mt-2"
                            onClick={handleUpdateSavingsGoal}
                        >
                            Update savings goal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
