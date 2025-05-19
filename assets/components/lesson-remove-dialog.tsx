import React, {useState} from 'react';
import Button from '@mui/material/Button';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogContentText from '@mui/material/DialogContentText';
import DialogTitle from '@mui/material/DialogTitle';
import axios from "axios";

interface FormDialogProps {
    open: boolean;
    handleClose: () => void;
    fetchLessons: () => void;
    handleShowSuccessRemoveAlert: () => void;
    lesson: {id: number, name: string}|null;
}

export default function LessonRemoveDialog({open, handleClose, lesson, fetchLessons, handleShowSuccessRemoveAlert}: FormDialogProps): React.ReactElement {
    const [isSaving, setIsSaving] = useState(false);

    const handleRemoveLesson = () => {
        /*if(name == '') {
            setNameError(true);
        } else {
            const formData = new FormData();
            formData.append('name', name);
            axios.post('/api/v1/lesson', formData)
                .then((response: any) => {
                    fetchLessons();
                    handleShowSuccessAlert();
                    resetForm();
                    handleClose();
                }).catch((error: any) => {
                console.error(error);
                setIsSaving(false);
            });
        }*/
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
                    <Button disabled={isSaving} onClick={handleRemoveLesson} type="submit">Usuń</Button>
                </DialogActions>
            </Dialog>
        </React.Fragment>
    );
}
