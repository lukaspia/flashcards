import React,{ useCallback } from "react";
import {
    IconButton,
    Tooltip
} from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';
import QuizIcon from '@mui/icons-material/Quiz';
import DeleteForeverIcon from '@mui/icons-material/DeleteForever';
import {Lesson} from "../../types/lesson.types";
import {useNavigate} from "react-router";
import {ROUTES} from "../../constants/Routes";
import {generatePath} from "../../utils/path-utils";

interface LessonRowProps {
    lesson: Lesson;
    handleRemoveClickOpen: (lesson: Lesson) => void;
}

export default function LessonRow({lesson, handleRemoveClickOpen}: LessonRowProps): React.ReactElement {
    const navigate = useNavigate();

    const handleRemoveLesson = useCallback(() => {
        handleRemoveClickOpen(lesson);
    }, [lesson, handleRemoveClickOpen]);

    const handleEditLesson = useCallback(() => {
        const path = generatePath(ROUTES.LESSON_EDIT, { id: lesson.id });
        navigate(path);
    }, [lesson.id, navigate]);

    const handleTestLesson = useCallback(() => {
        const path = generatePath(ROUTES.LESSON_TEST, { id: lesson.id });
        navigate(path);
    }, [lesson.id, navigate]);

    const wordsWithError = lesson.words ? lesson.words.filter(word => word.errors > 0) : [];
    const countWordsWithError = wordsWithError.length;

    return (
        <>
            <td>
                {lesson.name}
            </td>
            <td>
                {lesson.words.length}
                {countWordsWithError > 0 && ` (do powtórki ${countWordsWithError})`}
            </td>
            <td>
                <Tooltip title="Edytuj lekcję" placement="top-start">
                    <IconButton >
                        <EditIcon className="basic-icon" onClick={handleEditLesson} />
                    </IconButton>
                </Tooltip>
                <Tooltip title="Usuń lekcję" placement="top-start">
                    <IconButton >
                        <DeleteForeverIcon className="basic-icon" onClick={handleRemoveLesson} />
                    </IconButton>
                </Tooltip>
                <Tooltip title="Ucz się lub testuj" placement="top-start">
                    <IconButton >
                        <QuizIcon className="basic-icon" onClick={handleTestLesson} />
                    </IconButton>
                </Tooltip>
            </td>
        </>
    );
}