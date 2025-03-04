import React, { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import { useAppContext } from "../../context/AppContext.jsx";

export default function ChildCreatePage() {
    const navigate = useNavigate();
    const { householdId } = useParams();
    const [name, setName] = useState("");
    const [dateOfBirth, setDateOfBirth] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isFormDirty, setIsFormDirty] = useState(false);
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        const isDirty = name !== "" || dateOfBirth !== "";
        setIsFormDirty(isDirty);
    }, [name, dateOfBirth]);

    const handleSubmit = (event) => {
        event.preventDefault();
        setIsSubmitting(true);

        apiFetch(`households/${householdId}/children`, logout, {
            method: "POST",
            headers: {
                "Content-Type": "application/ld+json",
            },
            body: JSON.stringify({
                name,
                dateOfBirth,
            }),
        })
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                let id = data.id;
                navigate(`/household/${householdId}/child/${id}`);
            })
            .catch((error) => {
                console.error("Error adding child:", error);
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
        navigate(`/household/${householdId}`);
    };

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
                                onClick={handleBack}
                                className="btn btn-secondary"
                            >
                                &larr; Back to household
                            </button>
                        </div>
                        <h3 className="card-title text-primary mb-4">
                            Add Child
                        </h3>
                        <form onSubmit={handleSubmit}>
                            <div className="mb-3">
                                <label htmlFor="name" className="form-label">
                                    Child&apos;s Name
                                </label>
                                <input
                                    type="text"
                                    className="form-control"
                                    id="name"
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                    required
                                />
                            </div>
                            <div className="mb-3">
                                <label
                                    htmlFor="dateOfBirth"
                                    className="form-label"
                                >
                                    Date of Birth
                                </label>
                                <input
                                    type="date"
                                    className="form-control"
                                    id="dateOfBirth"
                                    value={dateOfBirth}
                                    onChange={(e) =>
                                        setDateOfBirth(e.target.value)
                                    }
                                />
                            </div>
                            <button
                                type="submit"
                                className="btn btn-primary"
                                disabled={isSubmitting}
                            >
                                {isSubmitting ? "Adding..." : "Add"}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}
