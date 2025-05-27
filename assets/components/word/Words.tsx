import React from "react";
import PlaylistAddIcon from "@mui/icons-material/PlaylistAdd";
import Button from "@mui/material/Button";
import Grid from '@mui/material/Grid';
import {TextField} from "@mui/material";

export default function Words(): React.ReactElement {
    return (
        <div className="lesson-words">
            <div>
                <h2>Lista słów</h2>
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
                    <div>$id</div>
                </Grid>
                <Grid size={5}>
                    <div>
                        <TextField
                            label="Przykład użycia"
                            multiline
                            rows={2}
                            maxRows={4}
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
                            maxRows={4}
                            variant="standard"
                        />
                    </div>
                </Grid>
                <Grid size={1}>
                    <div></div>
                </Grid>
            </Grid>


            <Button
                className="btn btn-primary"
                variant="contained"
                //onClick={handleSaveLesson}
                endIcon={<PlaylistAddIcon/>}>
                Dodaj
            </Button>
        </div>
    );
}