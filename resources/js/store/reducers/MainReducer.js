import { CSRF_TOKEN } from '../actionTypes/MainTypes';

/**
 * initial redux store state for CSRF
 */
const initialState = {
    csrf: "",
};

/**
 * Main Reducer
 *
 * @param   {object}  state         redux state
 * @param   {object}  initialState  our intial redux state
 * @param   {string}  action        reducer action
 *
 * @return  {object}                updated redux state
 */
const mainReducer = function (state = initialState, action) {
     switch (action.type) {
        case CSRF_TOKEN:
            return {
                ...state,
                csrf: document.querySelector('#root').getAttribute('data-token')
            };
        default:
            return state;
     }
};

export default mainReducer;
