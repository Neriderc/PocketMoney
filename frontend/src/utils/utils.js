// Function to calculate the brightness of a colour
export const getBrightness = (colour) => {
    colour = colour.replace("#", "");

    const r = parseInt(colour.substring(0, 2), 16);
    const g = parseInt(colour.substring(2, 4), 16);
    const b = parseInt(colour.substring(4, 6), 16);

    // Calculate brightness using the luminosity formula
    // From https://www.w3.org/TR/AERT/#color-contrast
    return (r * 299 + g * 587 + b * 114) / 1000;
};

export const getTextColourFromBrightness = (colour) => {
    if (getBrightness(colour) > 128) {
        return "#333333";
    } else {
        return "#DDDDDD";
    }
};

let isRefreshing = false;
let refreshPromise = null;

export async function apiFetch(apiResourcePath, logoutCallback, options = {}) {
    let token = localStorage.getItem("access_token");

    const response = await fetch("api/" + apiResourcePath, {
        ...options,
        headers: {
            ...options.headers,
            Authorization: `Bearer ${token}`,
        },
    });

    if (response.status === 401) {
        if (!isRefreshing) {
            isRefreshing = true;
            refreshPromise = tryRefreshToken();
        }

        const refreshed = await refreshPromise;
        isRefreshing = false;
        refreshPromise = null;

        if (refreshed) {
            return apiFetch(domain + "/api/" + apiResourcePath, options);
        } else {
            logoutCallback();
        }
    }

    return response;
}

async function tryRefreshToken() {
    try {
        const response = await fetch(domain + "/api/token/refresh", {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" },
        });

        if (!response.ok) throw new Error("Failed to refresh token");

        const data = await response.json();
        localStorage.setItem("access_token", data.token);
        return true;
    } catch (error) {
        console.error("Token refresh failed:", error);
        return false;
    }
}
