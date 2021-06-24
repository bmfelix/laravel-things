import { combineReducers } from 'redux';
import mainReducer  from './MainReducer';
import anoScanReducer  from './AnoScanReducer';

/**
 * combine reducers
 */
const rootReducer = combineReducers({
   mainReducer,
   anoScanReducer
});

export default rootReducer;
