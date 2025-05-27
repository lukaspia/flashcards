import React, {useState} from "react";
import PlaylistAddIcon from "@mui/icons-material/PlaylistAdd";
import PlaylistRemoveIcon from '@mui/icons-material/PlaylistRemove';
import Button from "@mui/material/Button";
import Grid from '@mui/material/Grid';
import {TextField} from "@mui/material";
import IconButton from "@mui/material/IconButton";

export default function Words(): React.ReactElement {
    const [words, setWords] = useState([{id: Date.now()}]);

    const handleAddWord = () => {
        setWords([...words, {id: Date.now()}]);
    }

    const handleRemoveWord = (idToRemove: number) => {
        setWords(words.filter(word => word.id !== idToRemove));
    }

    return (
        <div className="lesson-words">
            <div>
                <Grid container spacing={2}>
                    <Grid size={2}>
                        <div>
                            <h2>Lista słów</h2>
                        </div>
                    </Grid>
                    <Grid size={10}>
                        <div>
                            {words.length}
                        </div>
                    </Grid>
                </Grid>
            </div>

            <Grid container spacing={2}>
                <Grid size={{ xs: 6, md: 6 }}>
                    <div>
                        <h3>Słowa PL</h3>
                    </div>
                </Grid>
                <Grid size={{ xs: 6, md: 6 }}>
                    <div>
                        <h3>Słowa EN</h3>
                    </div>
                </Grid>
            </Grid>

            {words.map((word, key) => (
                <div key={word.id} className={`word-${word.id}`}>
                    <Grid container spacing={2}>
                        <Grid size={1}>
                            <div></div>
                        </Grid>
                        <Grid size={5}>
                            <div>
                                <TextField id="standard-basic" label="Nazwa pl" variant="standard" />
                            </div>
                        </Grid>
                        <Grid size={5}>
                            <div>
                                <TextField id="standard-basic" label="Nazwa en" variant="standard" />
                            </div>
                        </Grid>
                        <Grid size={1}>
                            <div>img</div>
                        </Grid>
                    </Grid>
                    <Grid container spacing={2}>
                        <Grid size={1}>
                            <div>{key + 1}</div>
                        </Grid>
                        <Grid size={5}>
                            <div>
                                <TextField
                                    label="Przykład użycia"
                                    multiline
                                    rows={2}
                                    variant="standard"
                                />
                            </div>
                        </Grid>
                        <Grid size={5}>
                            <div>
                                <TextField
                                    label="Przykład użycia"
                                    multiline
                                    rows={2}
                                    variant="standard"
                                />
                            </div>
                        </Grid>
                        <Grid size={1}>
                            <div>
                                {key > 0 && (
                                    <IconButton >
                                        <PlaylistRemoveIcon className="basic-icon" onClick={() => handleRemoveWord(word.id)} />
                                    </IconButton>
                                )}
                            </div>
                        </Grid>
                    </Grid>
                </div>
            ))}

            <Button
                className="btn btn-primary"
                variant="contained"
                disabled={words.length > 29}
                onClick={handleAddWord}
                endIcon={<PlaylistAddIcon/>}>
                Dodaj
            </Button>
        </div>
    );
}