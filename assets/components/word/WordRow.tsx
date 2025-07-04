import React, {useContext} from "react";
import {useSortable} from "@dnd-kit/sortable";
import {CSS} from "@dnd-kit/utilities";
import {
    Grid,
    TextField,
    IconButton,
    styled,
    InputLabel,
    MenuItem,
    FormControl,
    Select,
    Box,
} from '@mui/material';
import ClearIcon from "@mui/icons-material/Clear";
import CloudUploadIcon from "@mui/icons-material/CloudUpload";
import PlaylistRemoveIcon from "@mui/icons-material/PlaylistRemove";
import VolumeUpIcon from '@mui/icons-material/VolumeUp';
import {Word} from "@/types/word.types";
import WordsContext from "../../services/context/WordsContext";
import { useWordManagement } from "../../hooks/word/useWordManagement";

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
    index: number;
    keyId: number;
    word: Word;
}

export default function WordRow({ index, keyId, word}: WordRowProps) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
    } = useSortable({ id: keyId });

    const {
        wordState,
        handleUpdateWord,
        handleTranslateWord,
        handleRemoveWord,
        handleUploadImage,
        handleRemoveWordImage,
        handleReadText,
    } = useWordManagement({ initialWord: word, keyId });

    const {words, updateWords, wordsCategories} = useContext(WordsContext);

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        padding: '10px',
        margin: '5px 0',
        border: '1px solid lightgray',
    };

    return (
        <div key={wordState.id} className={`word-${wordState.id}`} ref={setNodeRef} style={style}>
            <Grid container spacing={2}>
                <Grid size={1}>
                    <div></div>
                </Grid>
                <Grid size={5}>
                    <div>
                        <TextField id="standard-basic" label="Nazwa pl" variant="standard" value={wordState.basicWord}
                                   onBlur={e => handleTranslateWord('basicWord')}
                                   onChange={e => handleUpdateWord('basicWord', e.target.value)}
                        />
                    </div>
                </Grid>
                <Grid size={5}>
                    <div>
                        <TextField id="standard-basic" label="Nazwa en" variant="standard" value={wordState.translation}
                                   onBlur={e => handleTranslateWord('translation')}
                                   onChange={e => handleUpdateWord('translation', e.target.value)}
                        />
                        <IconButton>
                            <VolumeUpIcon className="basic-icon" onClick={() => handleReadText(wordState.translation, 'translation')}/>
                        </IconButton>
                    </div>
                </Grid>
                <Grid size={1}>
                    <div>
                        <div>{wordState.image &&
                            <div className="image-container">
                                <img src={wordState.image} alt="Word illustration" className="small-image"/>
                                <IconButton>
                                    <ClearIcon className="basic-icon"
                                               onClick={handleRemoveWordImage}/>
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
                                        handleUploadImage(e.target.files);
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
                        {index + 1}
                    </div>
                </Grid>
                <Grid size={5}>
                    <div>
                        <input className="word-color" type="color" value={wordState.color && wordState.color.trim() !== '' ? wordState.color : '#000000'} onChange={e => handleUpdateWord('color', e.target.value)}/>
                    </div>
                    <div>
                        <FormControl variant="standard" sx={{ m: 1, minWidth: 120 }}>
                            <InputLabel>Kategoria</InputLabel>
                            <Select
                                id="word-category"
                                value={wordState.wordCategory?.id ?? (wordsCategories[0]?.id ?? '')}
                                onChange={e => handleUpdateWord('wordCategory', e.target.value)}
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
                            value={wordState.example}
                            onChange={(e) => {
                                handleUpdateWord('example', e.target.value)
                            }}
                        />
                        <IconButton>
                            <VolumeUpIcon className="basic-icon" onClick={() => handleReadText(wordState.example, 'example')}/>
                        </IconButton>
                    </div>
                </Grid>
                <Grid size={1}>
                    <div>
                        {words.length > 1 && (
                            <IconButton>
                                <PlaylistRemoveIcon className="basic-icon" onClick={handleRemoveWord}/>
                            </IconButton>
                        )}
                    </div>
                </Grid>
            </Grid>
        </div>
    );
}