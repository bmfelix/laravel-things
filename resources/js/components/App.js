import React from 'react'
import ReactDOM from 'react-dom'
import {BrowserRouter as Router, Switch, Route} from 'react-router-dom'
import Anodize from "./pages/anodize/Anodize"
import AnodizeMoveTag from './pages/anodize/AnodizeMoveTag'
import { Provider } from 'react-redux'
import store from '../store/CreateStore'

/**
 * subscribe to redux store updates
 */
store.subscribe(() => {})


/**
 * init our react app
 *
 * @return  {Router}  react router render
 */
function App() {
    return (
        <Provider store={store}>
            <Router>
            <>
                <Switch>
                    <Route path="/anodize/movetag" component={AnodizeMoveTag} />
                    <Route path="/anodize" component={Anodize} />
                </Switch>
            </>
            </Router>
        </Provider>
    );
}

export default App

//render the app into the root div
if (document.getElementById('root')) {
    ReactDOM.render(<App/>, document.getElementById('root'));
}
