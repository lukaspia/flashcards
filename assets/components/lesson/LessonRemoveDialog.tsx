import React, {useState, useEffect} from 'react';
import {
    Button,
    Dialog,
    DialogActions,
    DialogContent,
    DialogContentText,
    DialogTitle,
} from '@mui/material';
import {Lesson} from "../../types/lesson.types";
import {removeLessons} from "../../services/api/lessonApi";

interface FormDialogProps {
    open: boolean;
    handleClose: () => void;
    fetchLessons: () => void;
    handleShowSuccessRemoveAlert: () => void;
    lesson: Lesson|null;
}

export default function LessonRemoveDialog({open, handleClose, lesson, fetchLessons, handleShowSuccessRemoveAlert}: FormDialogProps): React.ReactElement {
    const [isRemoving, setIsRemoving] = useState(false);

    useEffect(() => {
        if (!open) {
            setIsRemoving(false);
        }
    }, [open]);

    const handleRemoveLesson = async () => {
        if (lesson === null) {
            console.warn("Attempted to remove a null lesson.");
            handleClose();
            return;
        }

        setIsRemoving(true);
        removeLessons(lesson)
        .then(() => {
            fetchLessons();
            handleShowSuccessRemoveAlert();
            handleClose();
        }).catch((error) => {
            console.error(error);
        }).finally(() => {
            setIsRemoving(false);
        });
    };

    return (
        <>
            <Dialog open={open} onClose={handleClose} aria-hidden={!open}>
                <DialogTitle>Usuwanie lekcji</DialogTitle>
                <DialogContent>
                    <DialogContentText>
                    Czy chcesz usunąć lekcję "{lesson !== null ? lesson.name : ''}"?
                    </DialogContentText>
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleClose} disabled={isRemoving}>Anuluj</Button>
                    <Button type="submit" disabled={isRemoving} onClick={handleRemoveLesson}>Usuń</Button>
                </DialogActions>
            </Dialog>
        </>
    );
}
