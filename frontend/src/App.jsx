import React from "react";
import { Routes, Route, Navigate } from "react-router-dom";
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap-icons/font/bootstrap-icons.css";
import "bootstrap/dist/js/bootstrap.bundle.min.js";

import LoginPage from "./pages/Login";
import Dashboard from "./pages/Dashboard";
import ChildPage from "./pages/Child/ChildDetails.jsx";
import EditChildPage from "./pages/Child/EditChild.jsx";
import AccountTransactionsPage from "./pages/Account/AccountDetails.jsx";
import CreateTransactionPage from "./pages/Transaction/CreateTransaction.jsx";
import TransactionDetailsPage from "./pages/Transaction/TransactionDetails.jsx";
import EditTransactionPage from "./pages/Transaction/EditTransaction.jsx";
import EditAccountPage from "./pages/Account/EditAccount.jsx";
import EditHouseholdPage from "./pages/Household/EditHousehold.jsx";
import CreateHouseholdPage from "./pages/Household/CreateHousehold.jsx";
import CreateChildPage from "./pages/Child/CreateChild.jsx";
import CreateAccountPage from "./pages/Account/CreateAccount.jsx";
import SettingsPage from "./pages/Settings";
import HouseholdDetailsPage from "./pages/Household/HouseholdDetails.jsx";
import CreateScheduledTransactionPage from "./pages/ScheduledTransaction/CreateScheduledTransaction.jsx";
import SchedulesTransactionDetailsPage from "./pages/ScheduledTransaction/ScheduledTransactionDetails.jsx";
import EditScheduledTransactionPage from "./pages/ScheduledTransaction/EditScheduledTransaction.jsx";
import UserDetailsPage from "./pages/User/UserDetails.jsx";
import EditUserPage from "./pages/User/EditUser.jsx";
import CreateUserPage from "./pages/User/CreateUser.jsx";
import ProtectedRoute from "./components/ProtectedRoute.jsx";
import WishlistItemDetailsPage from "./pages/Wishlist/WishlistItemDetails.jsx";
import EditWishlistItemPage from "./pages/Wishlist/EditWishlistItem.jsx";
import CreateWishlistItemPage from "./pages/Wishlist/CreateWishlistItem.jsx";
import EditSavingGoalPage from "./pages/Wishlist/EditSavingGoal.jsx";

function App() {
    return (
        <Routes>
            <Route path="/" element={<Navigate to="/dashboard" />} />
            <Route
                path="/login"
                element={
                    <ProtectedRoute>
                        <LoginPage />
                    </ProtectedRoute>
                }
            />
            <Route path="/dashboard" element={<Dashboard />} />
            <Route
                path="/household/:householdId/child/:childId"
                element={<ChildPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/edit"
                element={<EditChildPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/account/:accountId"
                element={<AccountTransactionsPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/account/add"
                element={<CreateAccountPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/account/:accountId/transaction/add"
                element={<CreateTransactionPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/wishlist/:wishlistId/wishlist_item/add"
                element={<CreateWishlistItemPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/scheduled_transaction/add"
                element={<CreateScheduledTransactionPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/scheduled_transaction/:scheduledTransactionId"
                element={<SchedulesTransactionDetailsPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/scheduled_transaction/:scheduledTransactionId/edit"
                element={<EditScheduledTransactionPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/account/:accountId/transaction/:transactionId"
                element={<TransactionDetailsPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/wishlist/:wishlistId/wishlist_item/:wishlistItemId"
                element={<WishlistItemDetailsPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/account/:accountId/transaction/:transactionId/edit"
                element={<EditTransactionPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/wishlist/:wishlistId/wishlist_item/:wishlistItemId/edit"
                element={<EditWishlistItemPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/wishlist/edit"
                element={<EditSavingGoalPage />}
            />
            <Route
                path="/household/:householdId/child/:childId/account/:accountId/edit"
                element={<EditAccountPage />}
            />
            <Route
                path="/household/:householdId/edit"
                element={<EditHouseholdPage />}
            />
            <Route
                path="/household/add"
                element={
                    <ProtectedRoute adminOnly>
                        <CreateHouseholdPage />
                    </ProtectedRoute>
                }
            />
            <Route
                path="/household/:householdId/child/add"
                element={<CreateChildPage />}
            />
            <Route
                path="/settings"
                element={
                    <ProtectedRoute>
                        <SettingsPage />
                    </ProtectedRoute>
                }
            />
            <Route
                path="/household/:householdId"
                element={<HouseholdDetailsPage />}
            />
            <Route
                path="/user/add"
                element={
                    <ProtectedRoute adminOnly>
                        <CreateUserPage />
                    </ProtectedRoute>
                }
            />
            <Route
                path="/user/:userId"
                element={
                    <ProtectedRoute adminOnly>
                        <UserDetailsPage />
                    </ProtectedRoute>
                }
            />
            <Route
                path="/user/:userId/edit"
                element={
                    <ProtectedRoute adminOnly>
                        <EditUserPage />
                    </ProtectedRoute>
                }
            />
        </Routes>
    );
}

export default App;
