import {
    ANO_NUMBER_BAR_CODES,
    ANO_TAG_SCANNED_SUCCESS,
    ANO_TAG_SCANNED_FAILURE,
    ANO_PRINT_BAR_CODES,
    ANO_CLOSE_MODAL,
    ANO_SHOW_LOAD_TAG_MODAL,
    ANO_SHOW_UNDELETE_MODAL,
    ANO_TAG_LOAD_STEP_SUCCESS,
    ANO_TAG_LOAD_STEP_FAILURE,
    ANO_GO_HOME
} from '../actionTypes/ScanTypes';

/**
 * initial redux store state
 */
const initialState = {
    record: {},
    scanned: false,
    number_of_barcodes: 0,
    success_message: "",
    error_message: "",
    validation_errors: null,
    step: 1,
    show: false,
    showError: false,
    showLoadTag: false,
    showUndelete: false,
    barcodes: {},
    loadTagNo: null,
    bar: {}
};

/**
 * Ano Scan Reducer
 *
 * @param   {object}  state         redux state
 * @param   {object}  initialState  our intial redux state
 * @param   {string}  action        reducer action
 *
 * @return  {object}                updated redux state
 */
const anoScanReducer = function (state = initialState, action) {
     switch (action.type) {
        case ANO_TAG_SCANNED_SUCCESS:
            return {
                ...state,
                record: action.data,
                scanned: true,
                success_message: "Successfully Got Data From AS400",
                error_message: "",
                step: 2,
                showError: false,
                showLoadTag: false,
                showUndelete: false,
                loadTagNo: null,
                bar: {}
            };
        case ANO_TAG_SCANNED_FAILURE:
            return {
                ...state,
                success_message: "",
                error_message: action.error,
                scanned: false,
                showError: true,
                showLoadTag: false,
                showUndelete: false,
                loadTagNo: null,
                step: 1,
                bar: {}
            };
        case ANO_TAG_LOAD_STEP_SUCCESS:
            return {
                ...state,
                record: action.movetag,
                scanned: true,
                success_message: "Successfully Loaded Step",
                error_message: "",
                step: parseInt(action.bar.step),
                bar: action.bar,
                showError: false,
                showLoadTag: false,
                showUndelete: false,
                loadTagNo: parseInt(action.bar.tag)
            };
        case ANO_TAG_LOAD_STEP_FAILURE:
            return {
                ...state,
                success_message: "",
                error_message: action.error,
                scanned: false,
                showError: true,
                showLoadTag: false,
                showUndelete: false,
                loadTagNo: null,
                step: 1,
                bar: {}
            };
        case ANO_NUMBER_BAR_CODES:
            return {
                ...state,
                success_message: "Updated # of bars to create",
                error_message: "",
                number_of_barcodes: action.data,
                step: 3,
                show: false,
                showError: false,
                showLoadTag: false,
                showUndelete: false,
                loadTagNo: null,
                bar: {}
            };
        case ANO_PRINT_BAR_CODES:
            return {
                ...state,
                success_message: "",
                error_message: "",
                step: 4,
                show: true,
                barcodes: action.data,
                showError: false,
                showLoadTag: false,
                showUndelete: false,
                loadTagNo: null,
                bar: {}
            };
        case ANO_CLOSE_MODAL:
            return {
                ...state,
                success_message: "",
                error_message: "",
                show: false,
                showError: false,
                showLoadTag: false,
                showUndelete: false,
                loadTagNo: null,
                bar: {}
            };
        case ANO_SHOW_LOAD_TAG_MODAL:
            return {
                ...state,
                success_message: "",
                error_message: "",
                show: false,
                showError: false,
                showLoadTag: true,
                showUndelete: false,
                loadTagNo: action.data,
                bar: {}
            };
        case ANO_SHOW_UNDELETE_MODAL:
            return {
                ...state,
                success_message: "",
                error_message: "",
                show: false,
                showError: false,
                showLoadTag: false,
                showUndelete: true,
                loadTagNo: action.data,
                bar: {}
            };
        case ANO_GO_HOME:
            return {
                ...initialState,
            };
        default:
            return state;
     }
};

export default anoScanReducer;
