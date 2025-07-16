import React, {useCallback} from 'react';
import TextField from "@mui/material/TextField";
import Button from "@mui/material/Button";
import SaveIcon from '@mui/icons-material/Save';
import {useNavigate, useParams} from "react-router";
import LoadingPreloader from "../components/ui/LoadingPreloader";
import Words from "../components/word/Words";
import CollapseSuccessAlert from "../components/ui/CollapseSuccessAlert";
import WordsContext from "../services/context/WordsContext";
import KeyboardReturnIcon from "@mui/icons-material/KeyboardReturn";
import IconButton from "@mui/material/IconButton";
import {generatePath} from "../utils/path-utils";
import {ROUTES} from "../constants/Routes";
import {useLessonEditData} from "../hooks/lesson/useLessonEditData";
import {useWordCategories} from "../hooks/word/useWordCategories";
import {useSuccessAlert} from "../hooks/useSuccessAlert";;

export default function LessonEdit(): React.ReactElement {
    const {id} = useParams();
    const lessonId = id ? parseInt(id) : 0;

    const { openSuccessAlert, successAlertMessage, showSuccessAlert, handleCloseSuccessAlert } = useSuccessAlert();

    const {
        editableLesson,
        isLoading,
        isSaving,
        isError,
        setEditableLesson,
        saveLesson
    } = useLessonEditData({lessonId, onSaveSuccess: () => showSuccessAlert('Lekcja została zaktualizowana.')});

    const categories = useWordCategories();
    const navigate = useNavigate();

    const handleSetLessonName = (name: string) => {
        if (editableLesson) {
            setEditableLesson({ ...editableLesson, name: name });
        }
    };

    const updateWords = useCallback((words: any) => {
        if (editableLesson) {
            setEditableLesson({ ...editableLesson, words: words });
        }
    }, [editableLesson, setEditableLesson]);

    const wordsContextValue = {
        words: editableLesson?.words || [],
        updateWords: updateWords,
        wordsCategories: categories,
        sourceLanguage: editableLesson?.sourceLanguage || '',
        targetLanguage: editableLesson?.targetLanguage || ''
    };

    const handleLessonList = () => {
        const path = generatePath(ROUTES.LESSON_PANEL);
        navigate(path);
    }

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
                    onClick={saveLesson}
                    endIcon={<SaveIcon />}
                    disabled={isSaving}
                >
                    {isSaving ? 'Zapisywanie...' : 'Zapisz'}
                </Button>
            </div>
        </div>
    );
}
