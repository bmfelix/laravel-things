import React, { Component } from 'react'
import axios from 'axios'

/**
 * renders Ano nav buttons
 * @extends Component
 */
class AnodizeNav extends Component {

    /**
     * init the class, init menu state
     * @param {object} props the props that we mapped below.
     */
    constructor(props) {
        super(props)
        this.state = {
            menu:  []
        }
    }

    /**
     * get the nav menu after the component is mounted
     */
    componentDidMount(){
        axios.get('/api/anodize/menu')
            .then(response => {
                this.setMenu(response.data.menu)
            })
            .catch(error=>{
                console.log(error)
            })
    }

    /**
     * set menu state
     */
    setMenu(response) {
        this.setState({menu: response})
    }

    render(){
        const menu = this.state.menu
        return(
            <ul className="col-6 offset-3 h-100 anodizeMenu">
                {menu.map((record, index) => {
                    let link = "/anodize/" + record.name;
                    return <li key={index} className="col-12">
                        <a href={link} className="btn btn-primary col-12">{record.text}</a>
                    </li>
                })}
            </ul>
        )
    }
}

export default AnodizeNav;
