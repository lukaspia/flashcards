import React from "react";
import IconButton  from "@mui/material/IconButton";
import EditIcon from '@mui/icons-material/Edit';
import QuizIcon from '@mui/icons-material/Quiz';
import DeleteForeverIcon from '@mui/icons-material/DeleteForever';
import {Lesson} from "./Lesson";

interface LessonRowProps {
    lesson: {id: number, name: string};
    handleRemoveClickOpen: (lesson: Lesson) => void;
}

export default function LessonRow({lesson, handleRemoveClickOpen}: LessonRowProps): React.ReactElement {

    const removeLesson = () => {
        handleRemoveClickOpen(lesson)
    }

    return (
        <>
            <td>
                {lesson.name}
            </td>
            <td>
                <IconButton ><EditIcon className="basic-icon" /></IconButton>
                <IconButton ><DeleteForeverIcon className="basic-icon" onClick={removeLesson} /></IconButton>
                <IconButton ><QuizIcon className="basic-icon" /></IconButton>
            </td>
        </>
    );
}