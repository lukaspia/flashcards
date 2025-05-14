import * as React from 'react';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogContentText from '@mui/material/DialogContentText';
import DialogTitle from '@mui/material/DialogTitle';

interface FormDialogProps {
    open: boolean;
    handleClose: () => void;
}

export default function FormDialog({open, handleClose}: FormDialogProps): React.ReactElement {
    return (
        <React.Fragment>
            <Dialog open={open} onClose={handleClose}>
                <DialogTitle>Dodawanie lekcji</DialogTitle>
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
                    />
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleClose}>Anuluj</Button>
                    <Button type="submit">Dodaj</Button>
                </DialogActions>
            </Dialog>
        </React.Fragment>
    );
}
