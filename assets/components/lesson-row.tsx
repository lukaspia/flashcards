import React from "react";
import Button from "@mui/material/Button";

interface LessonRowProps {
    lesson: {name: string};
}

export default function LessonRow({lesson}: LessonRowProps): React.ReactElement {
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