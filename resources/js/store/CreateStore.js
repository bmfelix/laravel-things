import { configureStore } from '@reduxjs/toolkit'
import { composeWithDevTools } from 'redux-devtools-extension';
import promise from "redux-promise";
import { createLogger } from 'redux-logger';
import { applyMiddleware } from 'redux';
import thunk from 'redux-thunk';
import rootReducer  from '../store/reducers/RootReducer';

/**
 * create logger
 */
const logger = createLogger();

/**
 * create store and export newly created store
 */
export default configureStore({
    reducer: {
        rootReducer
    }},
    composeWithDevTools(
        applyMiddleware(thunk, promise, logger),
    )
)
