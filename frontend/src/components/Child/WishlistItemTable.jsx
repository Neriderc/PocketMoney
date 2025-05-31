import React, { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useAppContext } from "../../context/AppContext.jsx";

export default function WishlistItemTable() {
    const { householdId, childId } = useParams();
    const navigate = useNavigate();
    const [wishlist, setWishlist] = useState([]);
    const [wishlistItems, setWishlistItems] = useState([]);
    const { apiFetch } = useAppContext();

    useEffect(() => {
        apiFetch(`children/${childId}/wishlists`)
            .then((response) => response.json())
            .then((data) => {
                if (data.member && Array.isArray(data.member)) {
                    setWishlist(data.member);
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
            .catch((error) => console.error("Error fetching wishlist:", error));
    }, [childId]);
    const handleCreateWishlistItem = () => {
        navigate(
            `/household/${householdId}/child/${childId}/wishlist/${wishlist[0].id}/wishlist_item/add`,
        );
    };

    function handleViewWishlistItem(wishlistItemId) {
        navigate(
            `/household/${householdId}/child/${childId}/wishlist/${wishlist[0].id}/wishlist_item/${wishlistItemId}`,
        );
    }

    return (
        <div
            className="card shadow-sm border-0 mb-4"
            style={{ backgroundColor: "#f8f9fa" }}
        >
            <div className="card-body">
                <div className="d-flex justify-content-between align-items-center mb-3">
                    <h3 className="card-title text-primary">Wishlist items</h3>
                    <button
                        onClick={handleCreateWishlistItem}
                        className="btn btn-success"
                    >
                        + Add
                    </button>
                </div>

                <div className="mt-3">
                    <table className="table table-striped">
                        <thead>
                            <tr>
                                <th className="d-none d-md-table-cell">
                                    Added
                                </th>
                                <th>Description</th>
                                <th className="d-none d-md-table-cell">
                                    Amount
                                </th>
                                <th className="d-none d-md-table-cell">
                                    Priority
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {wishlistItems.length > 0 ? (
                                wishlistItems.map((item) => (
                                    <tr key={item["@id"]}>
                                        <td className="d-none d-md-table-cell">
                                            {new Date(
                                                item.createdAt,
                                            ).toLocaleDateString()}
                                        </td>
                                        <td>{item.description}</td>
                                        <td className="d-none d-md-table-cell">
                                            ${item.amount.toFixed(2)}
                                        </td>
                                        <td className="d-none d-md-table-cell">
                                            {item.priority}
                                        </td>
                                        <td style={{ width: "100px" }}>
                                            <button
                                                onClick={() =>
                                                    handleViewWishlistItem(
                                                        item.id,
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
                                        No wishlist items available
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
