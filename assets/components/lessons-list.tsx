import React, {useEffect, useState} from 'react';
import Button from '@mui/material/Button';
import Pagination from '@mui/material/Pagination';
import AddLessonDialog from './lesson-dialog';
import LessonsListRows from "./lessons-list-rows";
import axios from "axios";

export default function LessonsList(): React.ReactElement {
    const [open, setOpen] = useState(false);
    const [lessons, setLessons] = useState([]);
    const [totalPages, setTotalPages] = useState(0);
    const [page, setPage] = useState(1);

    useEffect(() => {
        fetchLessons();
    }, []);

    const fetchLessons = (page: number = 1) => {
        axios.get("/api/v1/lessons?page=" + page)
            .then((response) => {
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

    const handlePaginationChange = (event: React.ChangeEvent<unknown>, value: number) => {
        fetchLessons(value);
    };

    return (
        <div className="lesson-list">
            <h1>Lista lekcji</h1>
            <Button className="btn btn-primary" variant="contained" onClick={handleClickOpen}>Dodaj lekcję</Button>
            <AddLessonDialog open={open} handleClose={handleClose} fetchLessons={fetchLessons} />
            <LessonsListRows lessons={lessons} />
            <Pagination count={totalPages} page={page} variant="outlined" shape="rounded" onChange={handlePaginationChange} />
        </div>
    );
};