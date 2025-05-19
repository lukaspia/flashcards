import React, {useEffect, useState} from 'react';
import Button from '@mui/material/Button';
import Pagination from '@mui/material/Pagination';
import AddLessonDialog from './lesson-dialog';
import LessonsListRows from "./lessons-list-rows";
import axios from "axios";
import AddIcon from "@mui/icons-material/Add";
import {Collapse, Alert, IconButton} from "@mui/material";
import CloseIcon from '@mui/icons-material/Close';
import LessonRemoveDialog from "./lesson-remove-dialog";
import Lesson from "./lesson";

export default function LessonsList(): React.ReactElement {
    const [open, setOpen] = useState(false);
    const [openSuccess, setOpenSuccess] = useState(false);
    const [successMessage, setSuccessMessage] = useState('');
    const [openRemove, setOpenRemove] = useState(false);
    const [lessonToRemove, setLessonToRemove] = useState<Lesson|null>(null);
    const [lessons, setLessons] = useState([]);
    const [totalPages, setTotalPages] = useState(0);
    const [page, setPage] = useState(1);

    useEffect(() => {
        fetchLessons();
    }, []);

    const fetchLessons = (page: number = 1) => {
        axios.get("/api/v1/lessons?page=" + page)
            .then((response) => {

                console.log(response.data.data);

                setLessons(response.data.data.lessons);
                setTotalPages(response.data.data.totalPages);
                setPage(page);
            })
            .catch((error) => {
                console.log(error);
            })
    }

    const handleClickOpen = () => {
        setOpen(true);
    };

    const handleClose = () => {
        setOpen(false);
    };

    const handleRemoveClickOpen = (lesson: Lesson) => {
        setLessonToRemove(lesson);
        setOpenRemove(true);
    }

    const handleRemoveClose = () => {
        setOpenRemove(false);
    }

    const handlePaginationChange = (event: React.ChangeEvent<unknown>, value: number) => {
        fetchLessons(value);
    };

    const handleShowSuccessAlert = () => {
        setSuccessMessage('Lekcja została dodana.');
        setOpenSuccess(true);
    }

    const handleShowSuccessRemoveAlert = () => {
        setSuccessMessage('Lekcja została usunięta.');
        setOpenSuccess(true);
    }

    return (
        <div className="lesson-list">
            <h1>Lista lekcji</h1>
            <Collapse in={openSuccess}>
                <Alert
                    action={
                        <IconButton
                            aria-label="close"
                            color="inherit"
                            size="small"
                            onClick={() => {
                                setOpenSuccess(false);
                            }}
                        >
                            <CloseIcon fontSize="inherit" />
                        </IconButton>
                    }
                    sx={{ mb: 2 }}
                >
                    {successMessage}
                </Alert>
            </Collapse>
            <Button className="btn btn-primary" variant="contained" onClick={handleClickOpen} endIcon={<AddIcon />}>Dodaj lekcję</Button>
            <AddLessonDialog open={open} handleClose={handleClose} fetchLessons={fetchLessons} handleShowSuccessAlert={handleShowSuccessAlert} />
            <LessonRemoveDialog open={openRemove} handleClose={handleRemoveClose} lesson={lessonToRemove} fetchLessons={fetchLessons} handleShowSuccessRemoveAlert={handleShowSuccessRemoveAlert} />
            <LessonsListRows lessons={lessons} handleRemoveClickOpen={handleRemoveClickOpen} />
            <Pagination count={totalPages} page={page} variant="outlined" shape="rounded" onChange={handlePaginationChange} />
        </div>
    );
};