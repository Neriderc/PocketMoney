import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import Select from "react-select";
import { useAppContext } from "../../context/AppContext.jsx";

export default function CreateUserPage() {
    const navigate = useNavigate();
    const [username, setUsername] = useState("");
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [roles, setRoles] = useState([]);
    const [households, setHouseholds] = useState([]);
    const [selectedHouseholds, setSelectedHouseholds] = useState([]);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("");
    const { apiFetch, logout } = useAppContext();

    const allRoles = [
        { value: "ROLE_USER", label: "User" },
        { value: "ROLE_ADMIN", label: "Admin" },
    ];

    useEffect(() => {
        apiFetch("households", logout)
            .then((res) => res.json())
            .then((data) => {
                setHouseholds(data.member || []);
            })
            .catch((error) => {
                console.error("Failed to load households", error);
                setMessage("Failed to load households.");
                setMessageType("danger");
            });
    }, []);

    const handleSubmit = (e) => {
        e.preventDefault();
        setIsSubmitting(true);
        setMessage("");
        setMessageType("");

        const payload = {
            username,
            email,
            password,
            roles,
            households: selectedHouseholds,
        };

        apiFetch("users", logout, {
            method: "POST",
            headers: {
                "Content-Type": "application/ld+json",
            },
            body: JSON.stringify(payload),
        })
            .then(async (res) => {
                if (!res.ok) {
                    const errorData = await res.json();
                    throw new Error(
                        errorData["description"] || "User creation failed.",
                    );
                }
                return res.json();
            })
            .then(() => {
                navigate("/settings");
            })
            .catch((error) => {
                console.error("Error creating user:", error);
                setMessage(error.message || "An error occurred.");
                setMessageType("danger");
                setIsSubmitting(false);
            });
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
                <div className="card shadow-sm border-0 mb-4">
                    <div className="card-body">
                        <h3 className="card-title text-primary mb-4">
                            Create New User
                        </h3>
                        <form onSubmit={handleSubmit}>
                            <div className="mb-3">
                                <label className="form-label">Username</label>
                                <input
                                    type="text"
                                    className="form-control"
                                    value={username}
                                    onChange={(e) =>
                                        setUsername(e.target.value)
                                    }
                                    required
                                />
                            </div>

                            <div className="mb-3">
                                <label className="form-label">Email</label>
                                <input
                                    type="email"
                                    className="form-control"
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                />
                            </div>

                            <div className="mb-3">
                                <label className="form-label">Password</label>
                                <input
                                    type="password"
                                    className="form-control"
                                    value={password}
                                    onChange={(e) =>
                                        setPassword(e.target.value)
                                    }
                                    required
                                />
                            </div>

                            <div className="mb-3">
                                <label className="form-label">Roles</label>
                                <Select
                                    isMulti
                                    options={allRoles}
                                    value={allRoles.filter((role) =>
                                        roles.includes(role.value),
                                    )}
                                    onChange={(selectedOptions) =>
                                        setRoles(
                                            selectedOptions.map(
                                                (opt) => opt.value,
                                            ),
                                        )
                                    }
                                    classNamePrefix="react-select"
                                />
                            </div>

                            <div className="mb-3">
                                <label className="form-label">Households</label>
                                <Select
                                    isMulti
                                    options={households.map((h) => ({
                                        value: h["@id"],
                                        label: h.name,
                                    }))}
                                    value={households
                                        .filter((h) =>
                                            selectedHouseholds.includes(
                                                h["@id"],
                                            ),
                                        )
                                        .map((h) => ({
                                            value: h["@id"],
                                            label: h.name,
                                        }))}
                                    onChange={(selectedOptions) =>
                                        setSelectedHouseholds(
                                            selectedOptions.map(
                                                (opt) => opt.value,
                                            ),
                                        )
                                    }
                                    classNamePrefix="react-select"
                                />
                            </div>

                            <button
                                type="submit"
                                className="btn btn-success"
                                disabled={isSubmitting}
                            >
                                {isSubmitting ? "Creating..." : "Create User"}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}
