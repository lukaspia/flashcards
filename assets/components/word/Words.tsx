import React, {useState} from "react";
import PlaylistAddIcon from "@mui/icons-material/PlaylistAdd";
import PlaylistRemoveIcon from '@mui/icons-material/PlaylistRemove';
import Button from "@mui/material/Button";
import Grid from '@mui/material/Grid';
import {TextField} from "@mui/material";
import IconButton from "@mui/material/IconButton";
import {Word} from "./Word";
import CloudUploadIcon from '@mui/icons-material/CloudUpload';
import { styled } from '@mui/material/styles';
import {uploadImage, removeWordImage} from "../../services/api/api";
import ClearIcon from '@mui/icons-material/Clear';

interface WordsProps {
    updateWords: (words: Word[]) => void;
    words: Word[];
}

const VisuallyHiddenInput = styled('input')({
    clip: 'rect(0 0 0 0)',
    clipPath: 'inset(50%)',
    height: 1,
    overflow: 'hidden',
    position: 'absolute',
    bottom: 0,
    left: 0,
    whiteSpace: 'nowrap',
    width: 1,
});

export default function Words({updateWords, words}: WordsProps): React.ReactElement {

    const emptyWord: Word = {
        id: Date.now(),
        basicWord: '',
        translation: '',
        example: '',
        image: ''
    };

    if(words.length == 0) {
        words.push(emptyWord);
    }

    const handleAddWord = () => {
        const newWords = [...words, emptyWord];
        updateWords(newWords);
    }

    const handleUpdateWord = (key: number, field: keyof Word, value: any) => {
        const newWords = [...words];

        if(field !== 'id') {
            (newWords[key] as any)[field] = value;
        }

        updateWords(newWords);
    }

    const handleRemoveWord = (idToRemove: number) => {
        const newWords = words.filter(word => word.id !== idToRemove);
        updateWords(newWords);
    }

    const handleUploadImage = (key: number, files: FileList | null, wordId: number) => {
        if(files && files.length > 0) {
            const file = files[0];

            const formData = new FormData();
            formData.append('image', file);
            formData.append('word', wordId as any as string);

            uploadImage(formData).then(res => {
                handleUpdateWord(key, 'image', res.data.image);
            });
        }
    }

    const handleRemoveWordImage = (key: number, wordId: number) => {
        removeWordImage(wordId).then(res => {
            handleUpdateWord(key, 'image', null);
        });
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
                                <TextField id="standard-basic" label="Nazwa pl" variant="standard" value={word.basicWord} onChange={(e) => {handleUpdateWord(key, 'basicWord', e.target.value)}} />
                            </div>
                        </Grid>
                        <Grid size={5}>
                            <div>
                                <TextField id="standard-basic" label="Nazwa en" variant="standard" value={word.translation} onChange={(e) => {handleUpdateWord(key, 'translation', e.target.value)}} />
                            </div>
                        </Grid>
                        <Grid size={1}>
                            <div>
                                <div>{word.image &&
                                    <div className="image-container">
                                        <img src={word.image} alt="Word illustration" className="small-image" />
                                        <IconButton >
                                                <ClearIcon className="basic-icon" onClick={() => handleRemoveWordImage(key, word.id)} />
                                        </IconButton>
                                    </div>
                                }
                                </div>
                                <div>
                                    <IconButton component="label">
                                        <CloudUploadIcon className="basic-icon" />
                                        <VisuallyHiddenInput
                                            type="file"
                                            onChange={(e) => {handleUploadImage(key, e.target.files, word.id);}}
                                            multiple
                                        />
                                    </IconButton>
                                </div>
                            </div>
                        </Grid>
                    </Grid>
                    <Grid container spacing={2}>
                        <Grid size={1}>
                            <div>{key + 1}</div>
                        </Grid>
                        <Grid size={5}>
                            <div>

                            </div>
                        </Grid>
                        <Grid size={5}>
                            <div>
                                <TextField
                                    label="Przykład użycia"
                                    multiline
                                    rows={2}
                                    variant="standard"
                                    value={word.example}
                                    onChange={(e) => {handleUpdateWord(key, 'example', e.target.value)}}
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