import React from 'react';
import Button from "./button";

export default function LessonsList(): React.ReactElement {
    return (
        <div className="lesson-list">
            <h1>Lista lekcji</h1>
            <Button title={"Dodaj lekcję"} type={"primary"} clickHandler={() => {console.log("Dodaj lekcję")}} />
        </div>
    );
};