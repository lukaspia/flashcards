import React, { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import {BrowserRouter as Router, Routes, Route} from "react-router";


function Main() {
    return(
        <Router>
            <Routes>
                <Route path="/panel" element={<h1>Home</h1>} />
            </Routes>
        </Router>
    );
}


const rootElement = document.getElementById("root");

const root = createRoot(rootElement!);

root.render(
    <StrictMode>
        <Main />
    </StrictMode>
);