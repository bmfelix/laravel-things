import React, { Component } from 'react'
import AnodizeNav from '../../nav/AnodizeNav'
import { connect } from 'react-redux'

/**
 * renders ano nav page
 * @extends Component
 */
class Anodize extends Component {

     /**
     * init the class.
     * @param {object} props the props that we mapped below.
     */
    constructor(props) {
        super(props)
    }

    render(){
        return(
            <div className="container-fluid main-container h-100 col-12 anodizeContainer">
                <h2>Anodize Department</h2>
                <div className="col-12 h-100 row">
                    <AnodizeNav />
                </div>
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

export default connect(mapStateToProps)(Anodize)
