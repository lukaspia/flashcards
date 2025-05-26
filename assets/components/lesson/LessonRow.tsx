import React from "react";
import IconButton  from "@mui/material/IconButton";
import EditIcon from '@mui/icons-material/Edit';
import QuizIcon from '@mui/icons-material/Quiz';
import DeleteForeverIcon from '@mui/icons-material/DeleteForever';
import {Lesson} from "./Lesson";
import {useNavigate} from "react-router";
import {ROUTES} from "../../constants/routes";
import {generatePath} from "../../utils/pathUtils";

interface LessonRowProps {
    lesson: {id: number, name: string};
    handleRemoveClickOpen: (lesson: Lesson) => void;
}

export default function LessonRow({lesson, handleRemoveClickOpen}: LessonRowProps): React.ReactElement {
    const navigate = useNavigate();

    const handleRemoveLesson = () => {
        handleRemoveClickOpen(lesson)
    }

    const handleEditLesson = () => {
        const path = generatePath(ROUTES.LESSON_EDIT, {id: lesson.id});
        navigate(path);
    }

    return (
        <>
            <td>
                {lesson.name}
            </td>
            <td>
                <IconButton >
                    <EditIcon className="basic-icon" onClick={handleEditLesson} />
                </IconButton>
                <IconButton >
                    <DeleteForeverIcon className="basic-icon" onClick={handleRemoveLesson} />
                </IconButton>
                <IconButton >
                    <QuizIcon className="basic-icon" />
                </IconButton>
            </td>
        </>
    );
}