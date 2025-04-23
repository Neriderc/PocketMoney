import { StrictMode } from "react";
import React from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import "./index.css";
import { AppProvider } from "./context/AppContext";
import App from "./App.jsx";

createRoot(document.getElementById("root")).render(
    <StrictMode>
        <BrowserRouter>
            <AppProvider>
                <App />
            </AppProvider>
        </BrowserRouter>
    </StrictMode>,
);
