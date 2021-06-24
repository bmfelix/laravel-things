import React, { Component } from 'react'
import AnodizeMoveTagScan from './AnodizeMoveTagScan'
import AnodizeMoveTagCreateBars from './AnodizeMoveTagCreateBars'
import AnodizeMoveTagTanks from './AnodizeMoveTagTanks'
import { connect } from 'react-redux'
import AnoModal from '../../modal/AnoModal'

/**
 * renders ano movetag page
 * @extends Component
 */
class AnodizeMoveTag extends Component {

    /**
     * init the class.
     * @param {object} props the props that we mapped below.
     */
    constructor(props) {
        super(props)
    }

    /**
     * set CSRF token when component mounts
     */
    componentDidMount(){
        this.props.dispatch({type: "CSRF_TOKEN"});
    }

    render(){
        let step = this.props.data.step
        let component = null;

        //render the appropriate component based on step
        const renderAnoStepComponent = () => {
            switch(step) {
                case 1:
                case 4:
                    component = <AnodizeMoveTagScan />
                    break;
                case 2:
                case 3:
                    component = <AnodizeMoveTagCreateBars />
                    break;
                case 5:
                    component = <AnodizeMoveTagTanks />
                    break;
            }
            return component
        }

        return(
            <div>
                <AnoModal show={this.props.data.show}/>
                { renderAnoStepComponent() }
            </div>
        )
    }
}

/**
 * Map our state to props
 * making them available on this.props
 *
 * @param {object} state  //our state from redux
 *
 * @return  {object} //our state mapped to props
 */
function mapStateToProps(state) {
    return {
        data: state.rootReducer.anoScanReducer,
        csrf: state.rootReducer.mainReducer.csrf
    }
}

export default connect(mapStateToProps)(AnodizeMoveTag)
