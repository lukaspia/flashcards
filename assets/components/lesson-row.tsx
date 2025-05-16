import React from "react";
import Button from "@mui/material/Button";
import IconButton  from "@mui/material/IconButton";
import EditIcon from '@mui/icons-material/Edit';
import QuizIcon from '@mui/icons-material/Quiz';
import DeleteForeverIcon from '@mui/icons-material/DeleteForever';

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
                <IconButton ><EditIcon className="basic-icon" /></IconButton>
                <IconButton ><DeleteForeverIcon className="basic-icon" /></IconButton>
                <IconButton ><QuizIcon className="basic-icon" /></IconButton>
            </td>
        </>
    );
}