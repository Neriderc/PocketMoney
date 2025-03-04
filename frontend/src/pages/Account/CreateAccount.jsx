import React, { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import NavBar from "../../components/NavBar.jsx";
import Select from "react-select";
import { CirclePicker } from "react-color";
import { useAppContext } from "../../context/AppContext.jsx";

const pickerColours = [
    "#ffffff",
    "#eb9694",
    "#fad0c3",
    "#fef3bd",
    "#c1e1c5",
    "#bedadc",
    "#bed3f3",
    "#d4c4fb",
    "#d9d9d9",
    "#ffe0b2",
    "#ffccbc",
    "#d7ccc8",
    "#cfd8dc",
    "#f0f4c3",
    "#dcedc8",
    "#ffcdd2",
    "#f8bbd0",
    "#e1bee7",
];

export default function AccountCreatePage() {
    const navigate = useNavigate();
    const { householdId, childId } = useParams();
    const [name, setName] = useState("");
    const [icon, setIcon] = useState("");
    const [color, setColor] = useState("#FFFFFF");
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isFormDirty, setIsFormDirty] = useState(false);
    const [iconOptions, setIconOptions] = useState([]);
    const { apiFetch, logout } = useAppContext();

    useEffect(() => {
        const isDirty = name !== "" || icon !== "" || color !== "#000000";
        setIsFormDirty(isDirty);

        fetch("/bootstrap-icons.json")
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((data) => {
                const iconArray = Object.keys(data).map((key) => ({
                    value: key,
                    label: data[key],
                }));
                setIconOptions(iconArray);
            })
            .catch((error) =>
                console.error("Error fetching icon options:", error),
            );
    }, [name, icon, color]);

    const handleSubmit = (event) => {
        event.preventDefault();
        setIsSubmitting(true);

        apiFetch(`children/${childId}/accounts`, logout, {
            method: "POST",
            headers: {
                "Content-Type": "application/ld+json",
            },
            body: JSON.stringify({
                name,
                icon,
                color,
            }),
        })
            .then((response) => {
                return response.json();
            })
            .then((json) => {
                navigate(
                    `/household/${householdId}/child/${childId}/account/${json.id}`,
                );
            })
            .catch((error) => {
                console.error("Error adding account:", error);
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
                                &larr; Back to child
                            </button>
                        </div>
                        <h3 className="card-title text-primary mb-4">
                            Add Account
                        </h3>
                        <form onSubmit={handleSubmit}>
                            <div className="mb-3">
                                <label htmlFor="name" className="form-label">
                                    Account Name
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
                                <label htmlFor="icon" className="form-label">
                                    Icon
                                </label>
                                <Select
                                    options={iconOptions}
                                    value={iconOptions.find(
                                        (option) => option.value === icon,
                                    )}
                                    onChange={(selectedOption) =>
                                        setIcon(selectedOption.value)
                                    }
                                    placeholder="Select an icon"
                                    formatOptionLabel={({ label, value }) => (
                                        <div>
                                            <i className={`bi ${value}`}></i>{" "}
                                            {label}
                                        </div>
                                    )}
                                />
                            </div>
                            <div className="mb-3">
                                <label htmlFor="color" className="form-label">
                                    Color
                                </label>
                                <div>
                                    <CirclePicker
                                        color={color}
                                        colors={pickerColours}
                                        onChange={(color) =>
                                            setColor(color.hex)
                                        }
                                    />
                                </div>
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
