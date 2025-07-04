import React, {useCallback, useEffect, useState} from 'react';
import TextField from "@mui/material/TextField";
import Button from "@mui/material/Button";
import SaveIcon from '@mui/icons-material/Save';
import useLesson from "../hooks/useLesson";
import {useNavigate, useParams} from "react-router";
import LoadingPreloader from "../components/ui/LoadingPreloader";
import Words from "../components/word/Words";
import {updateLesson} from "../services/api/lessonApi";
import CollapseSuccessAlert from "../components/ui/CollapseSuccessAlert";
import WordsContext from "../services/context/WordsContext";
import {getCategories} from "../services/api/wordApi";
import KeyboardReturnIcon from "@mui/icons-material/KeyboardReturn";
import IconButton from "@mui/material/IconButton";
import {generatePath} from "../utils/path-utils";
import {ROUTES} from "../constants/Routes";
import {Lesson} from "../types/lesson.types";

export default function LessonEdit(): React.ReactElement {
    const {id} = useParams();
    const [initialLesson, isLoading, isError] = useLesson(id ? parseInt(id): 0);
    const [editableLesson, setEditableLesson] = useState<Lesson | undefined>(undefined);

    const [isSaving, setIsSaving] = useState(false);
    const [openSuccessAlert, setOpenSuccessAlert] = useState(false);
    const [successAlertMessage, setSuccessAlertMessage] = useState('');
    const [categories, setCategories] = useState<any[]>([]);
    const navigate = useNavigate();

    useEffect(() => {
        if (initialLesson) {
            setEditableLesson(initialLesson);
        }
    }, [initialLesson]);

    const handleSetLessonName = (name: string) => {
        if (editableLesson) {
            setEditableLesson({ ...editableLesson, name: name });
        }
    };

    const handleSaveLesson = () => {
        setIsSaving(true);
        if (editableLesson) {
            updateLesson(editableLesson)
                .then((result) => {
                    setEditableLesson(result.data.lesson);
                    showSuccessAlert('Lekcja została zaktualizowana.');
                })
                .catch((error) => console.error(error))
                .finally(() => setIsSaving(false));
        }
    };

    const updateWords = useCallback((words: any) => {
        if (editableLesson) {
            setEditableLesson({ ...editableLesson, words: words });
        }
    }, [editableLesson]);

    const showSuccessAlert = useCallback((message: string) => {
        setSuccessAlertMessage(message);
        setOpenSuccessAlert(true);
    }, []);

    const handleCloseSuccessAlert = useCallback(() => {
        setSuccessAlertMessage('');
        setOpenSuccessAlert(false);
    }, []);

    const wordsContextValue = {
        words: editableLesson?.words || [],
        updateWords: updateWords,
        wordsCategories: categories,
    };

    const handleLessonList = () => {
        const path = generatePath(ROUTES.LESSON_PANEL);
        navigate(path);
    }

    useEffect(() => {
        getCategories().then((result) => {
            setCategories(result.data);
        });
    },[])

    return (
        <div className="lesson-edit">
            <div className="lesson-header">
                <IconButton onClick={handleLessonList}>
                    <KeyboardReturnIcon className="basic-icon"/>
                </IconButton>

                <h4>Edycja lekcji</h4>

                <LoadingPreloader isLoading={isLoading} />

                <TextField
                    className="lesson-name-input"
                    required
                    id="outlined-required"
                    label="Nazwa lekcji"
                    value={editableLesson?.name || ''}
                    onChange={(e) => handleSetLessonName(e.target.value)}
                />
            </div>

            <CollapseSuccessAlert
                openSuccess={openSuccessAlert}
                successMessage={successAlertMessage}
                handleCloseSuccessAlert={handleCloseSuccessAlert}
            />

            <div className="lesson-words-wrapper">
                <WordsContext value={wordsContextValue} >
                    <Words />
                </WordsContext>
            </div>

            <div className="lesson-footer">
                <Button
                    className="btn button-primary"
                    variant="contained"
                    onClick={handleSaveLesson}
                    endIcon={<SaveIcon />}>
                    Zapisz
                </Button>
            </div>
        </div>
    );
}
