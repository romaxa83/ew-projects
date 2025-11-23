export default function managerName(value) {
    value = parseInt(value);
    let user = window.managers[value] ? window.managers[value] : {name: 'Without user'};
    return user.name;
}
