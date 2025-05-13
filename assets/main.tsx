import React, { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import {BrowserRouter as Router, Routes, Route} from "react-router";

const NotFound = () => <h1>Page Not Found</h1>;

function Main() {
    return(
        <Router>
            <Routes>
                <Route path="/panel" element={<h1>Home</h1>} />
                <Route path="/*" element={<NotFound />} />
            </Routes>
        </Router>
    );
}


function initApp() {
    const rootElement = document.getElementById("root");

    if (!rootElement) {
        console.error("Root element not found.");
        return;
    }

    const root = createRoot(rootElement);

    root.render(
        <StrictMode>
            <Main />
        </StrictMode>
    );
}

initApp();