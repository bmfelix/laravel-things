//return CSRF token set by laravel, so we can store it redux and use across our frontend

const DefaultData = {

    get: () => {
        return document.querySelector('#root').getAttribute('data-token')
    }
};

export default DefaultData;
