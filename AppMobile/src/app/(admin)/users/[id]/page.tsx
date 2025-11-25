export const metadata = {
  title: "Admin User Details",
};

type PageParams = Promise<{ id: string }>;

export default async function Page({ params }: { params: PageParams }) {
  const { id } = await params;

  return (
    <main>
      <h1>Admin User Details</h1>
      <p>Inspect and update a specific user profile. Currently viewing user {id}.</p>
    </main>
  );
}
