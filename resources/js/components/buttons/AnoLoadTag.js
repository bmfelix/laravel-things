import React, { Component } from 'react'
import { connect } from 'react-redux'

/**
 * Component that renders the button to load the tag into anodize
 * @extends Component
 */
class AnoLoadTag extends Component {
    constructor(props) {
        super(props)
    }
    render() {
        return (
            <button
                id="loadTagButton"
                className="btn btn-danger col-6"
                onClick={this.props.onClick}
                tagno={this.props.tagno}
            >
                Load Tag
            </button>
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

export default connect(mapStateToProps)(AnoLoadTag)
