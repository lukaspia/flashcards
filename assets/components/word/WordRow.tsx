import {useSortable} from "@dnd-kit/sortable";
import {CSS} from "@dnd-kit/utilities";
import React, {useContext, useEffect, useState} from "react";
import Grid from "@mui/material/Grid";
import {TextField} from "@mui/material";
import IconButton from "@mui/material/IconButton";
import ClearIcon from "@mui/icons-material/Clear";
import CloudUploadIcon from "@mui/icons-material/CloudUpload";
import PlaylistRemoveIcon from "@mui/icons-material/PlaylistRemove";
import {styled} from "@mui/material/styles";
import {Word} from "@/types/word.types";
import WordsContext from "../../services/context/WordsContext";
import {uploadImage, removeWordImage} from "../../services/api/api";
import {translateWord} from "../../services/api/wordApi";
import VolumeUpIcon from '@mui/icons-material/VolumeUp';
import {readText} from "../../utils/text-reader";
import InputLabel from '@mui/material/InputLabel';
import MenuItem from '@mui/material/MenuItem';
import FormControl from '@mui/material/FormControl';
import Select, { SelectChangeEvent } from '@mui/material/Select';

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

interface WordRowProps {
    keyId: number;
    word: Word;
}

export default function WordRow({ keyId, word}: WordRowProps) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
    } = useSortable({ id: keyId });

    const {words, updateWords, wordsCategories} = useContext(WordsContext);
    const [slowRead, setSlowRead] = useState('');
    const [sourceLanguage, setSourceLanguage] = useState('pl-PL');
    const [targetLanguage, setTargetLanguage] = useState('en-US');

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        padding: '10px',
        margin: '5px 0',
        border: '1px solid lightgray',
    };

    const handleTranslateWord = (key: number, type: keyof Word, word: string) => {
        let translation: keyof Word;
        let translateFrom: string;
        let translateTo: string;

        if(type === 'basicWord') {
            translation = 'translation';
            translateFrom = sourceLanguage;
            translateTo = targetLanguage;
        } else {
            translation = 'basicWord';
            translateFrom = targetLanguage;
            translateTo = sourceLanguage;
        }

        if(words[key][translation] !== '') {
            return;
        }

        const promptData = {
            'word': word,
            'sourceLanguage': translateFrom,
            'targetLanguage': translateTo,
        }

        translateWord(promptData).then(res => {
            handleUpdateWord(key, translation, res.data.translation.translation);

            if(translation === 'translation') {
                handleUpdateWord(key, 'example', res.data.translation.example);
            }
        });
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

    const handleReadText = (text: string, key: number, type: string) => {
        if(slowRead == key + type) {
            readText(text, targetLanguage, 0.7);
            setSlowRead('');
        } else {
            readText(text, targetLanguage);
            setSlowRead(key + type);
        }
    }

    useEffect(() => {
        if(!word.wordCategory?.id) {
            handleUpdateWord(keyId, 'wordCategory', wordsCategories[0]?.id ?? '')
        }
    }, [wordsCategories]);

    return (
        <div key={word.id} className={`word-${word.id}`} ref={setNodeRef} style={style}>
            <Grid container spacing={2}>
                <Grid size={1}>
                    <div></div>
                </Grid>
                <Grid size={5}>
                    <div>
                        <TextField id="standard-basic" label="Nazwa pl" variant="standard" value={word.basicWord}
                                   onBlur={e => handleTranslateWord(keyId, 'basicWord', e.target.value)}
                                   onChange={e => handleUpdateWord(keyId, 'basicWord', e.target.value)}
                        />
                    </div>
                </Grid>
                <Grid size={5}>
                    <div>
                        <TextField id="standard-basic" label="Nazwa en" variant="standard" value={word.translation}
                                   onBlur={e => handleTranslateWord(keyId, 'translation', e.target.value)}
                                   onChange={e => handleUpdateWord(keyId, 'translation', e.target.value)}
                        />
                        <IconButton>
                            <VolumeUpIcon className="basic-icon" onClick={() => handleReadText(word.translation, keyId, 'translation')}/>
                        </IconButton>
                    </div>
                </Grid>
                <Grid size={1}>
                    <div>
                        <div>{word.image &&
                            <div className="image-container">
                                <img src={word.image} alt="Word illustration" className="small-image"/>
                                <IconButton>
                                    <ClearIcon className="basic-icon"
                                               onClick={() => handleRemoveWordImage(keyId, word.id)}/>
                                </IconButton>
                            </div>
                        }
                        </div>
                        <div>
                            <IconButton component="label">
                                <CloudUploadIcon className="basic-icon"/>
                                <VisuallyHiddenInput
                                    type="file"
                                    onChange={(e) => {
                                        handleUploadImage(keyId, e.target.files, word.id);
                                    }}
                                    multiple
                                />
                            </IconButton>
                        </div>
                    </div>
                </Grid>
            </Grid>
            <Grid container spacing={2}>
                <Grid size={1}>
                    <div>
                        <button {...attributes} {...listeners} className="drag-handle">
                            ⠿
                        </button>
                        {keyId + 1}
                    </div>
                </Grid>
                <Grid size={5}>
                    <div>
                        <input className="word-color" type="color" value={word.color && word.color.trim() !== '' ? word.color : '#000000'} onChange={e => handleUpdateWord(keyId, 'color', e.target.value)}/>
                    </div>
                    <div>
                        <FormControl variant="standard" sx={{ m: 1, minWidth: 120 }}>
                            <InputLabel>Kategoria</InputLabel>
                            <Select
                                id="word-category"
                                value={word.wordCategory?.id ?? (wordsCategories[0]?.id ?? '')}
                                onChange={e => handleUpdateWord(keyId, 'wordCategory', e.target.value)}
                                label="Kategoria"
                            >
                                {
                                    wordsCategories.map(
                                        (category) => (
                                            <MenuItem key={category.id} value={category.id}>{category.name}</MenuItem>
                                        )
                                    )
                                }
                            </Select>
                        </FormControl>
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
                            onChange={(e) => {
                                handleUpdateWord(keyId, 'example', e.target.value)
                            }}
                        />
                        <IconButton>
                            <VolumeUpIcon className="basic-icon" onClick={() => handleReadText(word.example, keyId, 'example')}/>
                        </IconButton>
                    </div>
                </Grid>
                <Grid size={1}>
                    <div>
                        {words.length > 1 && (
                            <IconButton>
                                <PlaylistRemoveIcon className="basic-icon" onClick={() => handleRemoveWord(word.id)}/>
                            </IconButton>
                        )}
                    </div>
                </Grid>
            </Grid>
        </div>
    );
}