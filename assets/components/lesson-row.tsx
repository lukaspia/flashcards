import React from "react";
import Button from "@mui/material/Button";

export default function LessonRow(lesson: any): React.ReactElement {
    return (
        <>
            <td>
                {lesson.name}
            </td>
            <td>
                opcje
            </td>
        </>
    );
}