import React, {useState, useEffect} from 'react';
import {
    Button,
    TextField,
    Dialog,
    DialogActions,
    DialogContent,
    DialogContentText,
    DialogTitle
} from '@mui/material';
import {addLesson} from "../../services/api/lessonApi";

interface FormDialogProps {
    open: boolean;
    handleClose: () => void;
    fetchLessons: () => void;
    handleShowSuccessAlert: () => void;
}

export default function AddLessonDialog({open, handleClose, fetchLessons, handleShowSuccessAlert}: FormDialogProps): React.ReactElement {
    const [name, setName] = useState('');
    const [nameError, setNameError] = useState(false);
    const [isSaving, setIsSaving] = useState(false);

    useEffect(() => {
        if (!open) {
            resetForm();
        }
    }, [open]);

    const resetForm = () => {
        setIsSaving(false);
        setName('');
        setNameError(false);
    };

    const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setName(e.target.value);
        if (nameError && e.target.value !== '') {
            setNameError(false);
        }
    };

    const handleAddLesson = async (event: React.FormEvent) => {
        event.preventDefault();

        if(name.trim() == '') {
            setNameError(true);
            return;
        }

        const formData = new FormData();
        formData.append('name', name);

        setIsSaving(true);
        addLesson(formData)
        .then(() => {
            fetchLessons();
            handleShowSuccessAlert();
            resetForm();
            handleClose();
        })
        .catch((error) => {
            console.error(error);
        }).finally(() => {
            setIsSaving(false);
        });
    };

    return (
        <>
            <Dialog open={open} onClose={handleClose} aria-hidden={!open}>
                <DialogTitle>Dodawanie lekcji</DialogTitle>
                <form onSubmit={handleAddLesson}>
                    <DialogContent>
                        <DialogContentText>
                            Wpisz nazwę lekcji
                        </DialogContentText>
                        <TextField
                            autoFocus
                            required
                            margin="dense"
                            id="lesson-name"
                            name="lesson_name"
                            label="Nazwa lekcji"
                            type="text"
                            fullWidth
                            variant="standard"
                            onChange={handleNameChange}
                            error={nameError}
                            helperText={nameError ? "Wprowadź nazwę lekcji" : ""}
                        />
                    </DialogContent>
                    <DialogActions>
                        <Button onClick={handleClose} disabled={isSaving}>Anuluj</Button>
                        <Button type="submit" disabled={isSaving}>Dodaj</Button>
                    </DialogActions>
                </form>
            </Dialog>
        </>
    );
}
