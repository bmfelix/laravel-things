import React, { Component } from 'react'
import { connect } from 'react-redux'
import AnoMoveTag from '../../../apis/AnoMoveTag';
import { Textbox } from 'react-inputs-validation';
import 'react-inputs-validation/lib/react-inputs-validation.min.css';

/**
 * renders ano tag tank input page
 * @extends Component
 */
class AnodizeMoveTagTanks extends Component {

    /**
     * init the class.
     * @param {object} props the props that we mapped below.
     */
    constructor(props) {
        super(props)
    }

    /**
     * handle on click event of save button
     * @param {event} e triggering event
     */
    handleOnClick (e){
        e.preventDefault()
        AnoMoveTag.saveTankInfo().then(response => {
            if (response.data.message.includes('tank info saved')) {
                this.props.dispatch({type: 'ANO_GO_HOME'});
            }
        })

    }

    render(){
        let data = this.props.data

        return(
            <div className="col-12 no-padding-margin">
                <div className="col-12 no-padding-margin">
                    <h3>Customer: <br />{ data.record.mmt_cusnam }</h3>
                    <table className="table" id="anoTagTable">
                        <thead className="head-light">
                            <tr>
                                <th scope="col">
                                    Tag No
                                </th>
                                <th scope="col">
                                    Lot No
                                </th>
                                <th scope="col">
                                    Die
                                </th>
                                <th scope="col">
                                    Length
                                </th>
                                <th scope="col">
                                    Qty
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">
                                   { data.record.mmt_tagno }
                                </th>
                                <td>
                                    { data.record.mmt_lotno }
                                </td>
                                <td>
                                    { data.record.mmt_die }
                                </td>
                                <td>
                                    { data.record.mmt_length }
                                </td>
                                <td className="totalQuantity">
                                    { data.record.mmt_qty }
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <form method="POST" name="anodizeTankInfo" id="anodizeTankInfo" className="col-12">
                    <div className="form-group row">
                        <div className="col-12">
                            <div className="container">
                                <Textbox
                                    attributesInput={{
                                        id: "tankNumber",
                                        name: "tankNumber",
                                        type: "number",
                                        placeholder: "Enter Tank Number",
                                        autoFocus: true
                                    }}
                                    defaultValue={data.bar.tank_num}
                                    disabled={false} // Optional.[Bool].Default: false.
                                    validate={true} // Optional.[Bool].Default: false. If you have a submit button and trying to validate all the inputs of your form at onece, toggle it to true, then it will validate the field and pass the result via the "validationCallback" you provide.
                                    validationCallback={res => {}} // Optional.[Func].Default: none. Return the validation result.
                                    classNameInput="form-control col-12 tankSpace" // Optional.[String].Default: "".
                                    onKeyUp={e => {}} // Required.[Func].Default: () => {}. Will return the value.
                                    onBlur={e => {}} // Optional.[Func].Default: none. In order to validate the value on blur, you MUST provide a function, even if it is an empty function. Missing this, the validation on blur will not work.
                                    validationOption={{
                                        name: "Tank Number", // Optional.[String].Default: "". To display in the Error message. i.e Please enter your ${name}.
                                        check: true, // Optional.[Bool].Default: true. To determin if you need to validate.
                                        required: true, // Optional.[Bool].Default: true. To determin if it is a required field.
                                        max: 2, // Optional.[Number].Default: 0. Validation of max length when validationOption['type'] is string, max amount when validationOption['type'] is number.
                                    }}
                                />
                            </div>
                            <div className="container">
                                <Textbox
                                    attributesInput={{
                                        id: "etchTime",
                                        name: "etchTime",
                                        type: "number",
                                        placeholder: "Enter Etch Time (in minutes)",
                                        autoFocus: true
                                    }}
                                    defaultValue={data.bar.etch_time}
                                    disabled={false} // Optional.[Bool].Default: false.
                                    validate={true} // Optional.[Bool].Default: false. If you have a submit button and trying to validate all the inputs of your form at onece, toggle it to true, then it will validate the field and pass the result via the "validationCallback" you provide.
                                    validationCallback={res => {}} // Optional.[Func].Default: none. Return the validation result.
                                    classNameInput="form-control col-12 tankSpace" // Optional.[String].Default: "".
                                    onKeyUp={e => {}} // Required.[Func].Default: () => {}. Will return the value.
                                    onBlur={e => {}} // Optional.[Func].Default: none. In order to validate the value on blur, you MUST provide a function, even if it is an empty function. Missing this, the validation on blur will not work.
                                    validationOption={{
                                        name: "Etch Time", // Optional.[String].Default: "". To display in the Error message. i.e Please enter your ${name}.
                                        check: true, // Optional.[Bool].Default: true. To determin if you need to validate.
                                        required: false, // Optional.[Bool].Default: true. To determin if it is a required field.
                                        max: 2, // Optional.[Number].Default: 0. Validation of max length when validationOption['type'] is string, max amount when validationOption['type'] is number.
                                    }}
                                />
                            </div>
                            <div className="container">
                                <input
                                    maxLength="2"
                                    placeholder="Enter Tank Time (in minutes)"
                                    type="number"
                                    className="form-control col-12 tankSpace"
                                    name="anoTime"
                                    defaultValue={data.bar.tank_time}
                                />
                            </div>
                            <div className="container">
                                <input
                                    maxLength="2"
                                    placeholder="Enter Tank Temperature"
                                    type="number"
                                    step="0.1"
                                    className="form-control col-12 tankSpace"
                                    name="anoTemp"
                                    defaultValue={data.bar.tank_temp}
                                />
                            </div>
                            <div className="container">
                                <button onClick={this.handleOnClick.bind(this)} className="btn btn-success col-12">Save</button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="_token" value={this.props.csrf} />
                    <input type="hidden" name="tag" value={data.bar.tag} />
                    <input type="hidden" name="bar" value={data.bar.number} />
                </form>
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

export default connect(mapStateToProps)(AnodizeMoveTagTanks)
