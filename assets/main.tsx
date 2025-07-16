import React, { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import {BrowserRouter as Router, Routes, Route} from "react-router";
import LessonsList from "./pages/LessonsList";
import LessonEdit from "./pages/LessonEdit";
import LessonTest from "./pages/Lesson";
import {ROUTES} from "./constants/Routes";

const NotFound = () => <h1>Page Not Found</h1>;

function Main() {
    return(
        <Router>
            <Routes>
                <Route path={ROUTES.LESSON_PANEL} element={<LessonsList />} />
                <Route path={ROUTES.LESSON_EDIT} element={<LessonEdit />} />
                <Route path={ROUTES.LESSON_TEST} element={<LessonTest />} />
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

document.addEventListener("turbo:load", initApp);