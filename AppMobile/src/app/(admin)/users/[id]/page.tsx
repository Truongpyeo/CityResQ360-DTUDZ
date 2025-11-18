export const metadata = {
  title: "Admin User Details",
};

export default function Page({ params }: { params: { id: string } }) {
  return (
    <main>
      <h1>Admin User Details</h1>
      <p>Inspect and update a specific user profile. Currently viewing user {params.id}.</p>
    </main>
  );
}
