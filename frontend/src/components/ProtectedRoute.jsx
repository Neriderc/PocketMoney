import { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAppContext } from "../context/AppContext";

export default function ProtectedRoute({ adminOnly = false, children }) {
    const { user, isAdmin, authChecked } = useAppContext();
    const navigate = useNavigate();

    useEffect(() => {
        if (authChecked) {
            if (!user) {
                navigate("/login");
            } else if (adminOnly && !isAdmin()) {
                navigate("/dashboard");
            }
        }
    }, [user, authChecked, adminOnly, navigate]);

    if (!authChecked) return <div>Loading...</div>;

    return children;
}
