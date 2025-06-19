import React from "react";
import IconButton  from "@mui/material/IconButton";
import EditIcon from '@mui/icons-material/Edit';
import QuizIcon from '@mui/icons-material/Quiz';
import DeleteForeverIcon from '@mui/icons-material/DeleteForever';
import {Lesson} from "./Lesson";
import {useNavigate} from "react-router";
import {ROUTES} from "../../constants/Routes";
import {generatePath} from "../../utils/PathUtils";

interface LessonRowProps {
    lesson: Lesson;
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

    const handleTestLesson = () => {
        const path = generatePath(ROUTES.LESSON_TEST, {id: lesson.id});
        navigate(path);
    }

    const wordsWithError = lesson.words ? lesson.words.filter(word => word.errors > 0) : [];
    const countWordsWithError = wordsWithError.length;

    return (
        <>
            <td>
                {lesson.name}
            </td>
            <td>
                {lesson.words.length} {countWordsWithError > 0 ? 'do powtórki ' + countWordsWithError : ''}
            </td>
            <td>
                <IconButton >
                    <EditIcon className="basic-icon" onClick={handleEditLesson} />
                </IconButton>
                <IconButton >
                    <DeleteForeverIcon className="basic-icon" onClick={handleRemoveLesson} />
                </IconButton>
                <IconButton >
                    <QuizIcon className="basic-icon" onClick={handleTestLesson} />
                </IconButton>
            </td>
        </>
    );
}