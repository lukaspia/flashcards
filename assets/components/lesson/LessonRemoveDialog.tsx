import React, {useState} from 'react';
import Button from '@mui/material/Button';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogContentText from '@mui/material/DialogContentText';
import DialogTitle from '@mui/material/DialogTitle';
import {Lesson} from "./Lesson";
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

    const handleRemoveLesson = async () => {
        if(lesson != null) {
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

        }
    };

    // @ts-ignore
    return (
        <React.Fragment>
            <Dialog open={open} onClose={handleClose} aria-hidden={!open}>
                <DialogTitle>Usuwanie lekcji</DialogTitle>
                <DialogContent>
                    <DialogContentText>
                    Czy chcesz usunąć lekcję "{lesson !== null ? lesson.name : ''}"?
                    </DialogContentText>
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleClose}>Anuluj</Button>
                    <Button disabled={isRemoving} onClick={handleRemoveLesson} type="submit">Usuń</Button>
                </DialogActions>
            </Dialog>
        </React.Fragment>
    );
}
